In currency-exchange application:
1. Install SQL SERVER database (MSSQL)
2. Rename .env.example to .env and replace with your database information

	DB_DATABASE=Your_database_name
	DB_USERNAME=Your_database_username
	DB_PASSWORD=Your_database_password
	
3. Run command php artisan migrate 

4. Run command php artisan serve --host=127.0.0.1 --port=8096
