import {
  ApiException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class Account extends BaseResource {
  async list(page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/accounts?page=${page}&per_page=${perPage}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/accounts/${accountId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async create(data) {
    this.validateCreateData(data);

    const headers = await this.getHeaders();
    const response = await this.client.post(
      "/api/v1.0/accounts",
      data,
      headers
    );

    if (!response.isSuccess()) {
      const error = response.getError();
      if (error?.errors) {
        throw new ValidationException(
          response.getMessage(),
          error.errors,
          response.getCode()
        );
      }
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async updateStatus(accountId, status) {
    if (!["active", "inactive"].includes(status)) {
      throw new ValidationException(
        'Status must be either "active" or "inactive"'
      );
    }

    const headers = await this.getHeaders();
    const body = { status };

    const response = await this.client.patch(
      `/api/v1.0/accounts/update-status/${accountId}`,
      body,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async delete(accountId) {
    const headers = await this.getHeaders();
    const response = await this.client.delete(
      `/api/v1.0/accounts/${accountId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  validateCreateData(data) {
    const required = ["name", "phone", "email"];
    const errors = {};

    for (const field of required) {
      if (!data[field]) {
        errors[field] = `The ${field} field is required`;
      }
    }

    // if (data.phone && !/^\d{9,15}$/.test(data.phone)) {
    //   errors.phone = "Phone must be 9-15 digits";
    // }

    // if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    //   errors.email = "Email must be a valid email address";
    // }

    // if (data.email && data.email.length > 100) {
    //   errors.email = "Email must not exceed 100 characters";
    // }

    if (Object.keys(errors).length > 0) {
      throw new ValidationException("Validation failed", errors);
    }
  }
}
