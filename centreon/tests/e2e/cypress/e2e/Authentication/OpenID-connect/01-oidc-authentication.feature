@REQ_MON-22122
Feature: OpenID Connect authentication
    As an admin of Centreon Platform
    I want to be able to use an external authentication provider
    So that Platform users can use existing authentication services to authenticate

@TEST_MON-22125
Scenario: Configure an authentication provider
    Given an administrator is logged on the platform
    When the administrator sets valid settings in the OpenID Connect configuration form and saves the form
    Then the configuration is saved and secrets are not visible

@TEST_MON-22123
Scenario: Default authentication mode Mixed
    Given an administrator is logged on the platform
    When the administrator configures the authentication mode
    Then default authentication mode must be Mixed and users created locally to centreon platform must be able to authenticate

@TEST_MON-22124
Scenario: Enable OpenID Connect authentication
    Given an administrator is logged on the platform
    When the administrator activates OpenID Connect authentication on the platform
    Then any user can authenticate using the authentication provider that is configured
