import { ApiException } from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class BalanceInquiry extends BaseResource {
  async getAccountBalance(accountId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/balance-inquiry/${accountId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async getMerchantBalance() {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      "/api/v1.0/balance-inquiry",
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }
}
