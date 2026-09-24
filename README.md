# MedQuery

> Simplifying Medicaid Data Access.

MedQuery is a lightweight web application designed to simplify access to Medicaid patient information, starting with Florida Medicaid.

The project aims to make patient eligibility verification and service history review faster and easier for healthcare providers, reducing the need to perform repetitive monthly searches through the Medicaid portal.

## Features

### Patient Eligibility Verification

* Search patient Medicaid eligibility information.
* Retrieve eligibility data for an entire calendar year.
* Avoid manually checking eligibility month by month.
* Present eligibility information in a clear and centralized view.

### Recipient Procedure Lookup

* Review services associated with a patient.
* Identify procedures and services reported through Medicaid.
* Display available service dates and relevant procedure information.
* Help providers determine whether specific services have been billed or paid.

## Project Status

**Early Development**

MedQuery is currently in its initial development stage. The first version focuses on simplifying Medicaid eligibility verification and recipient procedure lookup for Florida healthcare providers.

Additional features may be introduced as the project evolves.

## Goals

* Reduce administrative workload.
* Improve access to patient Medicaid information.
* Centralize eligibility and service history data.
* Provide a simple and efficient user experience.
* Build a foundation for additional healthcare-related tools.

## Technology

MedQuery is intentionally designed to remain lightweight and straightforward, focusing on simplicity, maintainability, and security.

* **Backend:** PHP
* **Database:** MySQL
* **Architecture:** Simple and maintainable application structure
* **Security:** Security-focused development practices, including input validation, authentication, authorization, and protection of sensitive patient information.

The project prioritizes a minimal technology stack without unnecessary complexity.

## Security

Security is a core consideration of MedQuery due to the sensitive nature of healthcare and patient information.

The project aims to follow security best practices, including:

* Secure authentication and session management.
* Input validation and sanitization.
* Protection against SQL injection.
* Protection against Cross-Site Request Forgery (CSRF).
* Proper authorization and access control.
* Secure handling of sensitive information.

## Disclaimer

MedQuery is an independent software project intended to assist healthcare providers with information retrieval and administrative workflows.

It is not affiliated with, endorsed by, or operated by Florida Medicaid or the Florida Agency for Health Care Administration (AHCA).

Users are responsible for ensuring that their use of Medicaid information complies with applicable laws, regulations, privacy requirements, and payer policies.

## License

MedQuery is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

See the [LICENSE](LICENSE) file for the full license text.

Copyright © 2026 MedQuery Contributors
