# Symfony Console CSV Reader Example

## Requirements
- php ^8.1

## Setup
```
composer install -a
```

## Usage
View CSV data from example file
```
php console csv:read ./data/example.csv
```

Run the test
```
php ./vendor/bin/phpunit tests/Command/CsvReaderCommandTest.php
```
