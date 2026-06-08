-- Grant the app user access to the test database (created by Symfony's test env)
GRANT ALL PRIVILEGES ON `minitrust_test%`.* TO 'minitrust'@'%';
FLUSH PRIVILEGES;

