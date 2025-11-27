import { describe, expect, it } from "@jest/globals";
import { Config } from "../../src/Config.js";

describe("Config", () => {
  it("should create config with required fields", () => {
    const config = new Config({
      clientId: "test-id",
      clientSecret: "test-secret",
      apiKey: "test-key",
    });

    expect(config.getClientId()).toBe("test-id");
    expect(config.getClientSecret()).toBe("test-secret");
    expect(config.getApiKey()).toBe("test-key");
  });

  it("should throw error when required fields are missing", () => {
    expect(() => {
      new Config({});
    }).toThrow();
  });

  it("should use default values", () => {
    const config = new Config({
      clientId: "test-id",
      clientSecret: "test-secret",
      apiKey: "test-key",
    });

    expect(config.getTimeout()).toBe(30000);
    expect(config.getMaxRetries()).toBe(3);
    expect(config.isAutoReauthEnabled()).toBe(true);
  });
});
