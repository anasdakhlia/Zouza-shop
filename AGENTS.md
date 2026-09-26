\# ZOUZA SHOP - AGENT DEVELOPMENT RULES



\## 1. PROJECT CONTEXT



This project is an online ordering website for a small Tunisian

pastry business specialized in Zouza.



The application has two main areas:



\- Front Office: customers browse products and place orders.

\- Back Office: administrators manage products, customers and orders.



The project is a real learning and portfolio project.



\---



\## 2. TECHNOLOGY STACK



Backend:

\- PHP 8.x

\- MySQL

\- PDO



Frontend:

\- HTML5

\- CSS3

\- Bootstrap

\- JavaScript



Architecture:

\- MVC



Development:

\- XAMPP

\- Git

\- GitHub



\---



\## 3. ARCHITECTURE RULES



Always respect MVC.



Controllers:

\- Handle HTTP requests.

\- Validate input.

\- Call models.

\- Select views.



Models:

\- Handle database operations.

\- Do not contain HTML.



Views:

\- Display information.

\- Do not contain database queries.



Do not mix business logic, SQL and HTML unnecessarily.



\---



\## 4. DATABASE RULES



Use PDO.



Always use prepared statements.



Never concatenate user input directly into SQL queries.



Never modify the database schema without first explaining:

\- what changes are required

\- why they are required

\- what existing functionality may be affected



Do not delete existing columns or tables without explicit approval.



\---



\## 5. SECURITY



Passwords must use:



password\_hash()



and



password\_verify()



Protect authenticated pages.



Validate all user input.



Escape output when appropriate.



Never hardcode passwords, API keys or secret credentials.



Never commit secrets to Git.



\---



\## 6. AGENT BEHAVIOR



Before implementing a significant feature:



1\. Explore the existing project.

2\. Identify relevant files.

3\. Understand the current architecture.

4\. Check dependencies and database impact.

5\. Explain the implementation plan.

6\. Identify potential risks.

7\. Then implement.



Do not immediately start coding.



\---



\## 7. CURIOSITY RULE



Be proactive.



When implementing a feature, investigate related parts of the project

that may be affected.



Look for:

\- existing reusable functions

\- duplicated code

\- inconsistent naming

\- security issues

\- broken references

\- database inconsistencies

\- missing validation

\- potential regressions



Do not silently make large architectural changes.



If you discover a problem outside the current task:



1\. Mention it.

2\. Explain its impact.

3\. Suggest a solution.

4\. Do not modify it unless necessary or explicitly approved.



\---



\## 8. VERIFICATION



After significant changes:



1\. Run appropriate tests.

2\. Check PHP syntax.

3\. Check database-related code.

4\. Check affected pages.

5\. Review the final diff.

6\. Report what was changed.

7\. Report remaining issues.



Never claim a feature works without verification.



\---



\## 9. CHANGE DISCIPLINE



Modify only files relevant to the current task.



Do not:

\- rewrite the whole project unnecessarily

\- change frameworks

\- install unnecessary dependencies

\- rename large numbers of files

\- change the database structure casually



Prefer small, understandable changes.



\---



\## 10. COMMUNICATION



Before major implementation, provide:



\### Understanding

What you believe the task means.



\### Plan

The files and steps you intend to modify.



\### Risks

Possible problems or side effects.



\### Verification

How you will test the result.



After implementation provide:



\### Changed

Files and functionality modified.



\### Tested

Tests and checks performed.



\### Issues

Anything still unresolved.



\---



\## 11. DEVELOPMENT STYLE



Keep the code understandable for a developer learning PHP MVC.



Prefer simple and maintainable solutions.



Do not over-engineer.



Avoid unnecessary abstractions.



Use clear names.



Comment only where comments provide useful context.



\---



\## 12. GIT



Do not rewrite Git history.



Do not delete .git.



Before risky operations, explain the operation.



Prefer small commits with meaningful messages.



Example:



feat: add product catalog



fix: validate checkout form



feat: implement shopping cart


