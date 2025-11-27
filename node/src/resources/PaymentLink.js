import {
  ApiException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class PaymentLink extends BaseResource {
  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/payment-link-manage/${accountId}?page=${page}&per_page=${perPage}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId, paymentLinkId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/payment-link-manage/${accountId}/${paymentLinkId}`,
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
      `/api/v1.0/payment-link-manage/${accountId}`,
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

  async update(accountId, paymentLinkId, data) {
    const headers = await this.getHeaders();
    const response = await this.client.put(
      `/api/v1.0/payment-link-manage/${accountId}/${paymentLinkId}`,
      data,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async delete(accountId, paymentLinkId) {
    const headers = await this.getHeaders();
    const response = await this.client.delete(
      `/api/v1.0/payment-link-manage/${accountId}/${paymentLinkId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async getAvailablePaymentMethods() {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      "/api/v1.0/payment-link-manage/payment-methods",
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  validateCreateData(data) {
    const required = ["reff_no", "title", "total_amount", "items"];
    const errors = {};

    for (const field of required) {
      if (!data[field]) {
        errors[field] = `The ${field} field is required`;
      }
    }

    if (data.items && !Array.isArray(data.items)) {
      errors.items = "The items field must be an array";
    }

    if (data.items && Array.isArray(data.items)) {
      data.items.forEach((item, index) => {
        if (!item.name) {
          errors[`items.${index}.name`] = "Item name is required";
        }
        if (item.quantity === undefined || typeof item.quantity !== "number") {
          errors[`items.${index}.quantity`] = "Item quantity must be numeric";
        }
        if (
          item.unit_price === undefined ||
          typeof item.unit_price !== "number"
        ) {
          errors[`items.${index}.unit_price`] =
            "Item unit price must be numeric";
        }
      });
    }

    if (data.total_amount && typeof data.total_amount !== "number") {
      errors.total_amount = "Total amount must be numeric";
    }

    if (
      data.max_usage &&
      (!Number.isInteger(data.max_usage) || data.max_usage < 1)
    ) {
      errors.max_usage = "Max usage must be a positive integer";
    }

    if (Object.keys(errors).length > 0) {
      throw new ValidationException("Validation failed", errors);
    }
  }
}
