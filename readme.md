# RuneManager for Old School RuneScape
**Welcome to RuneManager!**

RuneManager is a CMS developed for clans in Old School RuneScape. With RuneManager will you as a member of a clan have the possibility to communicate with each other in a more engaging and interactive way. By offering different content for each clan members outside the game such as news posting, events in-game, competeties, socializing and much more will you with RuneManager have a more complete feeling of being a member of clan outside the game!

**RuneManager is available to test on a [live demo](https://runemanager.habski.me)!**
[![N|Solid](https://i.imgur.com/Y0TKY30.png)](https://runemanager.habski.me)

## What is the point of this?
Why RuneManager? As a veteran of RuneScape I feel the social aspect of the game is important, and with that we have the in-game clans to gather a small community for your own. With a clan you have a structured way to have a group of people together in-game, and this is now even easier outside the game thanks to different medias such as Discord. However, there are not really any way to still have a communication with integrated elements in the game. By this I mean having a structured way to host events for your clan, notifying your clan with important updates or even have a way to see each other’s statistics without having to manually look them up.

RuneManager resolves this. As there is a lack of open-source applications for clan owners in RuneScape, I want to fill this gap and bring the possibility for every clans in RuneScape to provide such content. With RuneManager will you as a clan owner have a structured way of providing content such as these to your clan. By having a list of different features you will be able to integrate your clan members even more with each other outside the game.

# Main features
## News posting
Want to update your clan members on something? Do it by publishin a news post on your RuneManager application! This is an easy way for everyone to get the same update so there won't be any confusion. News posts also has a integrated comment system so people can discuss whatever that has been posted.

## Task system
RuneManger delivers a unique way of assigning different tasks in-game in a shape of a task system. The task system will bring a competitive aspect for the clan to compete against each other in order to obtain different perks within the clan where rewards are based on points. The points are differentiated after how difficult the task is, and  will have its own seperate hiscores for the clan members to compete against each other.

Examples on tasks are:

"Get 1 Angler piece"

"Get 5 uniques from medium clues"

"Complete the Ardougne Hard Diary"

For this to work clan members must install a RuneLite plugin created for this purpose. This plugin is another project related to RuneManager, and will be worked on when RuneManager is complete. Currently, the task system is purely individually so there are no hiscores yet and users of the application are free to assign and complete as many tasks as they wish.

## Calendar & Events planning
With the calendar, clan owners (and administrators) on RuneManager are able to provide clan members an easy way to inform and schedule when something clan related is about to happen. This calendar is synchronized with the clan events and follows the in-game time.

## Leaderboards
RuneManager allows clan members to show off their accomplishments. With the RuneLite plugin, clan members are able to show off their rare drops, milestones and much more that will be added later.

### Completed and fully functional
 - User registration & OSRS account linking
 - News
 - Hiscores (automatic scheduled updating)
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

# Quick Installation
If you are already familiar with Laravel and setting up a Laravel application with Nginx or Apache, just follow these instructions.
If not, visit our [wiki](https://github.com/Zlimon/RuneManager-OSRS/wiki/Installing-RuneManager-(Linux)-(MySQL)-(Nginx)) for clear instructions on how to install everything you need!

To have scheduled actions such as hiscore synchronization, it is recommended to host RuneManager with Linux

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
5. Generate an encryption key and create a symbolic link to the storage folder (this is where newspost images are stored).
```sh
php artisan key:generate
php artisan storage:link
```
6. Populate the database with the necassery tables and relations.
```sh
php artisan migrate:fresh
```
7. Create a crontab job schedule so the hiscores on the website are regularly updated.
```sh
crontab -e
* * * * * php /var/www/runemanager/artisan schedule:run
```
8. Change the group ownership of the storage and bootstrap/cache directories to www-data, and then recursively grant all permissions, including write and execute, to the group.
```sh
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

# Getting started
The first thing to do when RuneManager is properly set up is to create a Super Administrator account. Before making the application public you should register your own account and assign it the Super Administrator role. Do this by registering the account you want to be "Super Admin". The first account that is registered on the website is automatically assigned the Super Administrator role. If you want to change that you can do it in the Admin panel.

Then you should put a default image for newsposts. You can do it by placing a png image called default in the storage/image folder. This image is used for news post without any given image.

That's it!

### Tech
RuneManager uses two open source projects to work properly:
* [spatie/laravel-permission](https://github.com/spatie/laravel-permission) - Associate users with roles and permissions
* [unisharp/laravel-ckeditor](https://github.com/UniSharp/laravel-ckeditor) - This is a fork from the official CKEditor branch (standard edition), wrapped into the Laravel package.

### License
RuneManager is licensed under the MIT License. [See the license header in the respective file to be sure](https://github.com/Zlimon/RuneManager-OSRS/blob/master/LICENSE).

Reason for this is the different dependencies RuneManager uses, also including assets such as skill icons and images that are intellectual property and copyrighted by Jagex. This means RuneManager will only provide content that are limited within this license, and always ensures these assets are owned by Jagex.
