CHANGELOG
---------
### 1.0.0 (2023-10-04)
* Initial version

### 1.0.1 (2024-04-12)
* Updated API request methods and remove 'message'.
* Updated JS files and apply improvements by eslint.
* Update UI listing to show request body an response payload parsed by html entity.

### 1.0.2 (2024-04-23)
* Two observer are created for when the credit memo of the order is generated and the refund is carried out through ProntoPaga.
* Added configuration for the observer on system.xml
* Update validate action from Pronto Paga transaction UI grid.
* Added two new constants on Helper.
* Added new values on config.xml.
* Added new status `refuned`.
* Fix css.
* Rename function `validateOnCheckout` to `localValidation` to avoid confusion.
* Encrypted params ared added to  callbackUrl to validate the request and check the step.

### 1.0.3 (2024-06-10)
* Validate confirm payment method to check if uid is empty and return to failure page.
* Update Label for `specificmethods` configuration field and remove canRestore option.
* Update transactions repository get methods and update persistTransaction to load transaction by entity_id from order.
* Added try catch on `ProntoPagaController` create payment method to catch exceptions and return to failure page.

