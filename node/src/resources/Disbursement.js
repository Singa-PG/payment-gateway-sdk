import {
  ApiException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { Signature } from "../security/Signature.js";
import { BaseResource } from "./BaseResource.js";

export class Disbursement extends BaseResource {
  constructor(client, auth, config) {
    super(client, auth, config.getApiKey());
    this.config = config;
  }

  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/disbursement/${accountId}?page=${page}&per_page=${perPage}`,
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
      `/api/v1.0/disbursement/${accountId}/${transactionId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async checkFee(accountId, amount, bankSwiftCode) {
    const headers = await this.getHeaders();
    const body = {
      amount,
      bank_swift_code: bankSwiftCode,
    };

    const response = await this.client.post(
      `/api/v1.0/disbursement/${accountId}/check-fee`,
      body,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async checkBeneficiary(bankAccountNumber, bankSwiftCode) {
    const headers = await this.getHeaders();
    const body = {
      bank_account_number: bankAccountNumber,
      bank_swift_code: bankSwiftCode,
    };

    const response = await this.client.post(
      "/api/v1.0/disbursement/check-beneficiary",
      body,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async transfer(accountId, data) {
    this.validateTransferData(data);

    const headers = await this.getTransferHeaders(accountId, data);
    const response = await this.client.post(
      `/api/v1.0/disbursement/${accountId}/transfer`,
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

  validateTransferData(data) {
    const required = [
      "amount",
      "bank_swift_code",
      "bank_account_number",
      "reference_number",
    ];
    const errors = {};

    for (const field of required) {
      if (!data[field]) {
        errors[field] = `The ${field} field is required`;
      }
    }

    if (data.amount && (typeof data.amount !== "number" || data.amount <= 0)) {
      errors.amount = "Amount must be a positive number";
    }

    if (Object.keys(errors).length > 0) {
      throw new ValidationException("Validation failed", errors);
    }
  }

  async getTransferHeaders(accountId, body) {
    const timestamp = Math.floor(Date.now() / 1000);
    const endpoint = `/api/v1.0/disbursement/${accountId}/transfer`;
    const accessToken = await this.auth.getAccessToken();

    const signature = Signature.generateDisbursementSignature(
      "POST",
      endpoint,
      accessToken,
      body,
      timestamp,
      this.config.getClientSecret()
    );

    return {
      "X-PARTNER-ID": this.config.getApiKey(),
      Authorization: `Bearer ${accessToken}`,
      "X-Timestamp": timestamp.toString(),
      "X-Signature": signature,
      Accept: "application/json",
      "Content-Type": "application/json",
    };
  }
}
