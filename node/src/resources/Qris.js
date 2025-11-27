import {
  ApiException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class Qris extends BaseResource {
  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/qris-dynamic/${accountId}?page=${page}&per_page=${perPage}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId, qrisId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/qris-dynamic/${accountId}/show/${qrisId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async generate(accountId, data) {
    this.validateGenerateData(data);

    const headers = await this.getHeaders();
    const response = await this.client.post(
      `/api/v1.0/qris-dynamic/${accountId}/generate-qr`,
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

  async delete(qrisId) {
    const headers = await this.getHeaders();
    const response = await this.client.delete(
      `/api/v1.0/qris-dynamic/${qrisId}/delete`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  validateGenerateData(data) {
    const required = ["amount", "expired_at"];
    const errors = {};

    for (const field of required) {
      if (!data[field]) {
        errors[field] = `The ${field} field is required`;
      }
    }

    if (data.amount && (typeof data.amount !== "number" || data.amount <= 0)) {
      errors.amount = "Amount must be a positive number";
    }

    if (
      data.tip_indicator &&
      !["fixed_amount", "percentage"].includes(data.tip_indicator)
    ) {
      errors.tip_indicator =
        "Tip indicator must be either 'fixed_amount' or 'percentage'";
    }

    if (Object.keys(errors).length > 0) {
      throw new ValidationException("Validation failed", errors);
    }
  }
}
