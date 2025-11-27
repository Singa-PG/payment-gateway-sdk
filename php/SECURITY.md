# Security Policy

## Supported Versions

| Version | Supported |
| ------- | :-------: |
| 1.0.0   |    ✅     |

## Reporting a Vulnerability

We take the security of SingaPay SDK seriously. If you believe you have found a security vulnerability, please report it to us as described below.

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to [info@singapay.id](mailto:info@singapay.id).

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the following information in your report:

- Type of issue
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

## Best Practices

- Always use the latest version of the SDK
- Keep your API credentials secure
- Use environment variables for configuration
- Enable automatic token refresh
- Implement proper error handling
- Use Redis cache in production for better security
