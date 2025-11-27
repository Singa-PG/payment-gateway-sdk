import { SingaPay } from "./SingaPay.js";

/**
 * SingaPayFactory - Factory for managing SingaPay SDK instances
 *
 * Provides a centralized factory pattern for creating, managing, and reusing
 * SingaPay SDK instances with different configurations. Supports multiple
 * named instances, default configurations, and instance lifecycle management.
 *
 * This factory is particularly useful for applications that need to interact
 * with multiple SingaPay accounts or environments simultaneously.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class SingaPayFactory {
  /**
   * @private
   * @type {Map<string, SingaPay>}
   */
  static instances = new Map();

  /**
   * @private
   * @type {object|null}
   */
  static defaultConfig = null;

  /**
   * Create or retrieve a SingaPay SDK instance
   *
   * Creates a new SingaPay instance with the given configuration and name,
   * or returns an existing instance if one with the same name already exists.
   * This ensures singleton-like behavior for each named instance.
   *
   * @param {object} config Configuration object for SingaPay
   * @param {string} [name="default"] Instance name identifier
   * @returns {SingaPay} SingaPay SDK instance
   *
   * @example
   * // Create a default instance
   * const singapay = SingaPayFactory.create({
   *   clientId: 'client1',
   *   clientSecret: 'secret1',
   *   apiKey: 'key1',
   *   environment: 'sandbox'
   * });
   *
   * @example
   * // Create multiple named instances
   * const production = SingaPayFactory.create(prodConfig, 'production');
   * const sandbox = SingaPayFactory.create(sandboxConfig, 'sandbox');
   */
  static create(config, name = "default") {
    if (this.instances.has(name)) {
      return this.instances.get(name);
    }

    const instance = new SingaPay(config);
    this.instances.set(name, instance);

    return instance;
  }

  /**
   * Get an existing SingaPay instance by name
   *
   * @param {string} [name="default"] Instance name identifier
   * @returns {SingaPay} SingaPay SDK instance
   * @throws {Error} If instance with the given name does not exist
   *
   * @example
   * const singapay = SingaPayFactory.get('production');
   * const balance = await singapay.balanceInquiry.get();
   */
  static get(name = "default") {
    if (!this.instances.has(name)) {
      throw new Error(`SingaPay instance '${name}' not found`);
    }

    return this.instances.get(name);
  }

  /**
   * Set default configuration for factory-created instances
   *
   * @param {object} config Default configuration object
   * @returns {void}
   *
   * @example
   * SingaPayFactory.setDefaultConfig({
   *   clientId: 'default-client',
   *   clientSecret: 'default-secret',
   *   apiKey: 'default-key',
   *   environment: 'sandbox'
   * });
   */
  static setDefaultConfig(config) {
    this.defaultConfig = config;
  }

  /**
   * Create instance using default configuration
   *
   * @param {string} [name="default"] Instance name identifier
   * @returns {SingaPay} SingaPay SDK instance
   * @throws {Error} If default configuration is not set
   *
   * @example
   * SingaPayFactory.setDefaultConfig(defaultConfig);
   * const singapay = SingaPayFactory.createWithDefault();
   */
  static createWithDefault(name = "default") {
    if (this.defaultConfig === null) {
      throw new Error("Default configuration not set");
    }

    return this.create(this.defaultConfig, name);
  }

  /**
   * Check if an instance exists
   *
   * @param {string} [name="default"] Instance name identifier
   * @returns {boolean} True if instance exists
   *
   * @example
   * if (SingaPayFactory.has('production')) {
   *   console.log('Production instance exists');
   * }
   */
  static has(name = "default") {
    return this.instances.has(name);
  }

  /**
   * Remove an instance from the factory
   *
   * @param {string} [name="default"] Instance name identifier
   * @returns {void}
   *
   * @example
   * SingaPayFactory.remove('sandbox');
   */
  static remove(name = "default") {
    this.instances.delete(name);
  }

  /**
   * Get all registered instance names
   *
   * @returns {string[]} Array of instance names
   *
   * @example
   * const instanceNames = SingaPayFactory.getInstanceNames();
   * console.log('Active instances:', instanceNames);
   */
  static getInstanceNames() {
    return Array.from(this.instances.keys());
  }

  /**
   * Create multiple instances from a configuration object
   *
   * @param {object} configs Object with instance names as keys and configurations as values
   * @returns {object} Object with instance names as keys and SingaPay instances as values
   *
   * @example
   * const instances = SingaPayFactory.createMultiple({
   *   client1: { clientId: 'client1', clientSecret: 'secret1', apiKey: 'key1' },
   *   client2: { clientId: 'client2', clientSecret: 'secret2', apiKey: 'key2' },
   *   client3: { clientId: 'client3', clientSecret: 'secret3', apiKey: 'key3' }
   * });
   *
   * // Use specific instance
   * await instances.client1.disbursement.create({ ... });
   */
  static createMultiple(configs) {
    for (const [name, config] of Object.entries(configs)) {
      this.create(config, name);
    }

    return Object.fromEntries(this.instances);
  }
}
