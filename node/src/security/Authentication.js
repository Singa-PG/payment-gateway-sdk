import { AuthenticationException } from "../exceptions/SingaPayException.js";
import { Signature } from "./Signature.js";

/**
 * Authentication - SingaPay API Authentication Handler
 *
 * Handles the complete authentication lifecycle for SingaPay API interactions,
 * including token acquisition, caching, refresh, and management. This class
 * implements the OAuth 2.0 client credentials flow specifically designed for
 * SingaPay's B2B API authentication.
 *
 * Supports both in-memory and persistent cache backends for token storage
 * to optimize performance and reduce authentication requests.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Authentication {
  /**
   * Creates a new Authentication instance
   *
   * @param {object} config Configuration object containing client credentials
   * @param {object|null} client HTTP client instance (can be set later with setClient())
   * @param {object|null} cache Cache instance for token storage (optional)
   *
   * @example
   * const auth = new Authentication(config, httpClient, cache);
   *
   * // Or initialize client later
   * const auth = new Authentication(config);
   * auth.setClient(httpClient);
   */
  constructor(config, client = null, cache = null) {
    /**
     * @private
     * @type {object}
     */
    this.config = config;
    /**
     * @private
     * @type {object|null}
     */
    this.client = client;
    /**
     * @private
     * @type {object|null}
     */
    this.cache = cache;
    /**
     * @private
     * @type {string|null}
     */
    this.accessToken = null;
  }

  /**
   * Set HTTP client for authentication requests
   *
   * Allows deferred initialization of the HTTP client if not provided
   * in the constructor.
   *
   * @param {object} client HTTP client instance
   * @returns {void}
   *
   * @example
   * auth.setClient(httpClient);
   */
  setClient(client) {
    this.client = client;
  }

  /**
   * Get current access token
   *
   * Retrieves the current access token from memory cache, persistent cache,
   * or performs a new authentication if no valid token is available.
   *
   * @returns {Promise<string>} Promise resolving to valid access token
   *
   * @throws {AuthenticationException} If authentication fails or client not initialized
   *
   * @example
   * try {
   *   const token = await auth.getAccessToken();
   *   console.log('Access token:', token);
   * } catch (error) {
   *   console.error('Authentication failed:', error.message);
   * }
   */
  async getAccessToken() {
    if (this.accessToken) {
      return this.accessToken;
    }

    if (this.cache) {
      const cachedToken = await this.cache.get("access_token");
      if (cachedToken) {
        this.accessToken = cachedToken;
        return cachedToken;
      }
    }

    await this.authenticate();
    return this.accessToken;
  }

  /**
   * Perform authentication with SingaPay API
   *
   * Executes the client credentials flow to obtain a new access token.
   * Generates the required cryptographic signature and handles the
   * authentication API response.
   *
   * @returns {Promise<string>} Promise resolving to new access token
   *
   * @throws {AuthenticationException} If authentication request fails or response is invalid
   *
   * @example
   * try {
   *   const token = await auth.authenticate();
   *   console.log('New access token obtained:', token);
   * } catch (error) {
   *   console.error('Authentication failed:', error.message);
   * }
   */
  async authenticate() {
    if (!this.client) {
      throw new AuthenticationException("HTTP client not initialized");
    }

    try {
      const { timestamp, signature } = Signature.generateAuthSignature(
        this.config.getClientId(),
        this.config.getClientSecret()
      );

      const response = await this.client.post(
        "/api/v1.1/access-token/b2b",
        {
          grant_type: "client_credentials",
        },
        {
          "X-CLIENT-ID": this.config.getClientId(),
          "X-PARTNER-ID": this.config.getApiKey(),
          "X-Timestamp": timestamp.toString(),
          "X-Signature": signature,
          Accept: "application/json",
          "Content-Type": "application/json",
        }
      );

      if (!response.isSuccess()) {
        throw new AuthenticationException(
          response.getMessage() || "Authentication failed",
          response.getStatusCode()
        );
      }

      const data = response.getData();
      this.accessToken = data.access_token || data.data?.access_token;

      if (!this.accessToken) {
        throw new AuthenticationException("No access token in response");
      }

      if (this.cache) {
        const expiresIn = data.expires_in || data.data?.expires_in || 3600;
        await this.cache.set("access_token", this.accessToken, expiresIn - 60);
      }

      return this.accessToken;
    } catch (error) {
      if (error instanceof AuthenticationException) {
        throw error;
      }
      throw new AuthenticationException(
        "Authentication request failed: " + error.message,
        0,
        error
      );
    }
  }

  /**
   * Refresh access token
   *
   * Invalidates the current token and performs a new authentication.
   * This method is typically called when a token expires or becomes invalid.
   *
   * @returns {Promise<string>} Promise resolving to new access token
   *
   * @throws {AuthenticationException} If refresh operation fails
   *
   * @example
   * try {
   *   const newToken = await auth.refreshToken();
   *   console.log('Token refreshed:', newToken);
   * } catch (error) {
   *   console.error('Token refresh failed:', error.message);
   * }
   */
  async refreshToken() {
    this.accessToken = null;
    if (this.cache) {
      await this.cache.delete("access_token");
    }
    
    return this.authenticate();
  }

  /**
   * Check if currently authenticated
   *
   * Verifies whether a valid access token is available in memory.
   * Note: This does not validate the token against the API or check
   * cache expiration.
   *
   * @returns {boolean} True if access token is present in memory
   *
   * @example
   * if (auth.isAuthenticated()) {
   *   console.log('Already authenticated');
   * } else {
   *   console.log('Need to authenticate');
   * }
   */
  isAuthenticated() {
    return this.accessToken !== null;
  }
}
