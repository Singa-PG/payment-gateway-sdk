import { ApiException } from "../exceptions/SingaPayException.js";
import { BaseResource } from "./BaseResource.js";

export class Statement extends BaseResource {
  async list(accountId, page = 1, perPage = 25, filters = {}) {
    const headers = await this.getHeaders();
    const queryParams = new URLSearchParams({
      page,
      per_page: perPage,
      ...(filters.start_date && { start_date: filters.start_date }),
      ...(filters.end_date && { end_date: filters.end_date }),
    });

    const response = await this.client.get(
      `/api/v1.0/statements/${accountId}?${queryParams}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }

  async get(accountId, statementId) {
    const headers = await this.getHeaders();
    const response = await this.client.get(
      `/api/v1.0/statements/${accountId}/${statementId}`,
      headers
    );

    if (!response.isSuccess()) {
      throw new ApiException(response.getMessage(), response.getCode());
    }

    return response.getData();
  }
}
