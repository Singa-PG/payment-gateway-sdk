import { ApiException } from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class PaymentLinkHistory extends BaseResource {
  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/payment-link-histories/${accountId}?page=${page}&per_page=${perPage}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId, historyId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/payment-link-histories/${accountId}/${historyId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }
}
