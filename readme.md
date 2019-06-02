# RuneManager for Old School RuneScape
RuneManager is a web application for managing your clan and clan members in RuneScape. The purpose of this application is to be able to have an overview of your clan, clan members and their statistics such as their levels and experience etc. Furthermore, you will be able to have a level of management on the website such as posting news, events or articles related to your clan. There will also be tracking of events in-game related to clan members such as achievements, rare drops and hiscores to make clan members able to compete with eachother via the web application. With this you will also be able to have an easy way to promote you clan both in-game and on the internet.

RuneManager is available to test on a [live demo](https://runemanager.habski.me)!
[![N|Solid](https://i.imgur.com/Y0TKY30.png)](https://runemanager.habski.me)

# Features
## Task system
RuneManger delivers an unique way of completing different tasks in-game in a shape of a task system. The task system will bring a competetive aspect for the clan to compete against eachother in order to obtain different perks within the clan.

## Calendar


### Completed and fully functional
 - User registration & OSRS account linking
 - News
 - Hiscores
 - (Simplified) Task system

### Planned and WIP features
- [ ] Admin panel
    - [X] Users (profiles)
    - [X] Members (Linked OSRS accounts)
        - [X] Preregistration of accounts
        - [X] Edit (Transfer ownership)
    - [X] News
    - [ ] Events
    - [ ] Task system (administration)
    - [ ] Ranks and permissions system
    - [ ] Ban and user deletion system
    - [ ] Mail system(?)
- [ ] Calendar (for in-game events)
- [ ] Task leaderboards
- [ ] Data grabbing system (Runelite plugin)
    - [ ] Loot
    - [ ] Achievements
    - [ ] Integration with task system
- [ ] Customizable application design
- [X] Discord widget

**Todos for the future**
 - [ ] Integrate Ironman hiscores
    - [ ] Integration with Group Ironman when released

# Installation
If you are already familiar with Laravel and setting up a Laravel application with Nginx or Apache, just follow these instructions.
If not, visit our wiki for clear instructions on how to install everything you need!

## Linux
1. Create a folder and give your current user ownership of it.
```sh
sudo mkdir -p /var/www/html/runemanager
sudo chown $USER:$USER /var/www/html/runemanager
```
2. Clone RuneManager via Git to the newly created folder.
```sh
cd /var/www/html/runemanager
git clone https://github.com/Zlimon/RuneManager-OSRS.git .
```
3. Next, install the RuneManager dependencies with Composer and clean up the installation files.
```sh
composer install
php artisan clear-compiled
```
4. Copy the .env-example file into a file named .env, and edit the .env file with your favourite editor and set the environment variables.
5. Generate an encryption key and link a storage syslink (this is where newspost images are stored).
```sh
php artisan key:generate
php artisan storage:link
```
6. Populate the database with the necassery tables and relations.
```sh
php artisan migrate:fresh
```
7. Change the group ownership of the storage and bootstrap/cache directories to www-data, and then recursively grant all permissions, including write and execute, to the group.
```sh
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

## Windows

### Tech
RuneManager uses two open source projects to work properly:
* [spatie/laravel-permission](https://github.com/spatie/laravel-permission) - Associate users with roles and permissions
* [unisharp/laravel-ckeditor](https://github.com/UniSharp/laravel-ckeditor) - This is a fork from the official CKEditor branch (standard edition), wrap it to laravel package.

# Getting started

### License
RuneManager is licensed under the MIT License. See the license header in the respective file to be sure.
