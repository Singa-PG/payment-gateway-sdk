import { ApiException } from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class VATransaction extends BaseResource {
  async list(accountId, page = 1, perPage = 25) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/va-transactions/${accountId}?page=${page}&per_page=${perPage}`,
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
      `/api/v1.0/va-transactions/${accountId}/${transactionId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }
}
