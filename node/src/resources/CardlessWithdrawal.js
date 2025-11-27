import {
  ApiException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class CardlessWithdrawal extends BaseResource {
  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/cardless-withdrawals/${accountId}?page=${page}&per_page=${perPage}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId, transactionId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/cardless-withdrawals/${accountId}/show/${transactionId}`,
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
      `/api/v1.0/cardless-withdrawals/${accountId}`,
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

  async cancel(accountId, transactionId) {
    const headers = await this.getHeaders();
    const response = await this.client.patch(
      `/api/v1.0/cardless-withdrawals/${accountId}/cancel/${transactionId}`,
      null,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async delete(accountId, transactionId) {
    const headers = await this.getHeaders();
    const response = await this.client.delete(
      `/api/v1.0/cardless-withdrawals/${accountId}/delete/${transactionId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  validateCreateData(data) {
    const required = ["withdraw_amount", "payment_vendor_code"];
    const errors = {};

    for (const field of required) {
      if (!data[field]) {
        errors[field] = `The ${field} field is required`;
      }
    }

    if (
      data.withdraw_amount &&
      (typeof data.withdraw_amount !== "number" || data.withdraw_amount <= 0)
    ) {
      errors.withdraw_amount = "Withdraw amount must be a positive number";
    }

    if (Object.keys(errors).length > 0) {
      throw new ValidationException("Validation failed", errors);
    }
  }
}
