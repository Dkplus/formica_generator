Feature: Generating domain events

    As a programmer
    I want to generate domain events
    So that I save time

    Scenario: Generating a domain event
        Given the namespace "Acme\" is placed in the directory "src"
        When I generate a domain event "Acme\UserHasBeenCreated" with the following properties:
            | property | type    |
            | id       | int     |
            | email    | string  |
        Then the class "Acme\UserHasBeenCreated" should exist in the file "src/UserHasBeenCreated.php"
        And the class "Acme\UserHasBeenCreated" should be annotated with "Dkplus\Annotations\DDD\DomainEvent"
        And the class "Acme\UserHasBeenCreated" should be final
        And the class "Acme\UserHasBeenCreated" should be constructed with 2 parameters:
            | parameter | type    |
            | id        | int     |
            | email     | string  |
        And the class "Acme\UserHasBeenCreated" should have 3 methods:
            | method     | return type       |
            | id         | int               |
            | email      | string            |
            | occurredOn | DateTimeImmutable |
