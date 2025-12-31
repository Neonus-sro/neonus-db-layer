# Neonus DB Layer

A lightweight PHP database abstraction layer built on top of **Doctrine DBAL** with a repository pattern implementation.

## Overview

This package provides a clean, extensible database layer for PHP applications using:
- **Doctrine DBAL** for database interactions
- **Repository Pattern** for data access abstraction
- **Entity pattern** for object-oriented data representation
- **Factory pattern** for entity creation

## Features

- 🔧 **BaseRepository** - Abstract repository with common database operations
- 🏗️ **BaseEntity** - Abstract entity class with standard properties and methods
- 📦 **EntityFactory** - Factory for creating entity instances from data
- 🔌 **Extensible interfaces** - Easy to implement custom repositories and entities
- ✨ **Type-safe operations** - Parameter type handling for DBAL

## Installation

```bash
composer require neonus/db-layer
```

## Requirements

- PHP 7.4+
- Doctrine DBAL ^3.0 or ^4.0
- Symfony Dependency Injection ^8.0

## Quick Start

### 1. Create an Entity

Extend `BaseEntity` to define your domain model:

```php
namespace App\Entity;

use Neonus\DbLayer\Entity\BaseEntity;

class User extends BaseEntity
{
    protected string $name = '';
    protected string $email = '';
    protected string $url = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // Add more getters/setters...
}
```

### 2. Create a Repository

Extend `BaseRepository` to implement data access logic:

```php
namespace App\Repository;

use Neonus\DbLayer\Repository\BaseRepository;
use App\Entity\User;

class UserRepository extends BaseRepository
{
    public const TABLE_NAME = 'users';
    public const ENTITY_NAME = User::class;

    // Inherit basic CRUD operations, or add custom queries
}
```

### 3. Use the Repository

```php
$userRepository = new UserRepository($connection, $entityFactory);

// Retrieve a user by ID
$user = $userRepository->get(1);

// Retrieve a user by URL
$user = $userRepository->getByUrl('john-doe');

// Create/Update a user
$user->setName('John Doe');
$userRepository->persist($user);

// Delete all records
$userRepository->deleteAll();
```

## API Reference

### BaseRepository

#### Methods

- **`get(int $id): BaseEntity|bool`** - Fetch a single entity by ID
- **`getByUrl(string $url): BaseEntity|bool`** - Fetch a single entity by URL
- **`persist(BaseEntity $entity, bool $forceInsert = false): int`** - Save or update an entity
- **`deleteAll(): int`** - Delete all records from the table
- **`getDb(): Connection`** - Access the DBAL connection directly

### BaseEntity

#### Properties

- **`id`** - Primary key (auto-managed)
- **`created_at`** - Creation timestamp (DateTime)

#### Methods

- **`getId(): int`** - Get the entity ID
- **`setId(int $id): void`** - Set the entity ID
- **`getCreatedAt(): DateTime`** - Get creation timestamp
- **`setCreatedAt(mixed $created_at): void`** - Set creation timestamp
- **`toArray(): array`** - Convert entity to array

## Project Structure

```
src/
├── Entity/
│   └── BaseEntity.php              # Base entity class
├── Repository/
│   └── BaseRepository.php          # Base repository class
├── Lib/
│   └── EntityFactory.php           # Entity factory
└── Interfaces/
    ├── RepositoryInterface.php     # Repository interface
    ├── HydratationInterface.php    # Hydration interface
```

## License

Proprietary - NEONUS, s.r.o.

## Author

NEONUS, s.r.o. (info@neonus.sk)
