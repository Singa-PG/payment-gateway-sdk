import {
  ApiException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class VirtualAccount extends BaseResource {
  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/virtual-accounts/${accountId}?page=${page}&per_page=${perPage}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId, vaId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/virtual-accounts/${accountId}/${vaId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async create(accountId, data) {
    this.validateCreateData(data);

    const headers = await this.getHeaders();
    const response = await this.client.post(
      `/api/v1.0/virtual-accounts/${accountId}`,
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

  async update(accountId, vaId, data) {
    const headers = await this.getHeaders();
    const response = await this.client.put(
      `/api/v1.0/virtual-accounts/${accountId}/${vaId}`,
      data,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async delete(accountId, vaId) {
    const headers = await this.getHeaders();
    const response = await this.client.delete(
      `/api/v1.0/virtual-accounts/${accountId}/${vaId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  validateCreateData(data) {
    const required = ["bank_code", "amount", "kind"];
    const errors = {};

    for (const field of required) {
      if (!data[field]) {
        errors[field] = `The ${field} field is required`;
      }
    }

    if (data.kind && !["temporary", "permanent"].includes(data.kind)) {
      errors.kind = "Kind must be either 'temporary' or 'permanent'";
    }

    if (data.kind === "temporary" && !data.expired_at) {
      errors.expired_at = "The expired_at field is required for temporary VA";
    }

    if (data.max_usage && data.max_usage > 255) {
      errors.max_usage = "Max Usage is 255";
    }

    if (Object.keys(errors).length > 0) {
      throw new ValidationException("Validation failed", errors);
    }
  }
}
