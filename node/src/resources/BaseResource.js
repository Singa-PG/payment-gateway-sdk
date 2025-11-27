/**
 * BaseResource - Base class for all API resources
 *
 * Provides common functionality for all API resource classes including
 * authentication header management, token handling, and request preparation.
 * This class serves as the foundation for all SingaPay API service classes.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class BaseResource {
  /**
   * Creates a new BaseResource instance
   *
   * @param {Client} client HTTP client instance for making API requests
   * @param {Authentication} auth Authentication handler for token management
   * @param {string} apiKey API key for partner identification
   * @param {boolean} [needToken=true] Whether this resource requires access token for requests
   *
   * @example
   * // Resource that requires authentication
   * class MyResource extends BaseResource {
   *   constructor(client, auth, apiKey) {
   *     super(client, auth, apiKey, true);
   *   }
   * }
   *
   * @example
   * // Resource that doesn't require authentication
   * class PublicResource extends BaseResource {
   *   constructor(client, auth, apiKey) {
   *     super(client, auth, apiKey, false);
   *   }
   * }
   */
  constructor(client, auth, apiKey, needToken = true) {
    /**
     * @protected
     * @type {Client}
     */
    this.client = client;

    /**
     * @protected
     * @type {Authentication}
     */
    this.auth = auth;

    /**
     * @protected
     * @type {string}
     */
    this.apiKey = apiKey;

    /**
     * @protected
     * @type {boolean}
     */
    this.needToken = needToken;
  }

  /**
   * Get request headers with authentication
   *
   * Generates the appropriate HTTP headers for API requests, including
   * authentication tokens when required. Automatically handles token
   * retrieval and validation based on the resource's configuration.
   *
   * @returns {Promise<object>} Promise resolving to headers object with properties:
   * @returns {string} returns['X-PARTNER-ID'] API key for partner identification
   * @returns {string} [returns.Authorization] Bearer token when authentication is required
   * @returns {string} returns.Accept Accept header for JSON responses
   * @returns {string} returns['Content-Type'] Content type for JSON requests
   *
   * @throws {Error} When API key is missing or access token is required but not available
   *
   * @example
   * const headers = await this.getHeaders();
   * // Returns: {
   * //   'X-PARTNER-ID': 'your-api-key',
   * //   'Authorization': 'Bearer eyJhbGciOiJIUzI1NiIs...', // Optional by condition
   * //   'Accept': 'application/json',
   * //   'Content-Type': 'application/json'
   * // }
   */
  async getHeaders() {
    const apiKey = this.auth?.config?.getApiKey?.() || this.apiKey;

    if (!apiKey) {
      throw new Error("API Key is required for request headers");
    }

    if (!this.needToken) {
      return {
        "X-PARTNER-ID": apiKey,
        Accept: "application/json",
        "Content-Type": "application/json",
      };
    }

    const token = await this.auth.getAccessToken();

    if (!token) {
      throw new Error("Access token is required for request headers");
    }

    return {
      "X-PARTNER-ID": apiKey,
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
      "Content-Type": "application/json",
    };
  }
}
