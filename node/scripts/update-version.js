import { readFileSync, writeFileSync } from "fs";
import { dirname, join } from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const packageJsonPath = join(__dirname, "..", "package.json");
const packageJson = JSON.parse(readFileSync(packageJsonPath, "utf8"));

const versionContent = `// src/version.js - Auto-generated file - do not edit manually

/**
 * SDK Version - Auto-generated during build process
 * @constant {string} SDK_VERSION
 */
export const SDK_VERSION = "${packageJson.version}";
`;

// Tulis ke file version.js
const versionFilePath = join(__dirname, "..", "src", "version.js");
writeFileSync(versionFilePath, versionContent, "utf8");

console.log(`Version updated to: ${packageJson.version}`);
