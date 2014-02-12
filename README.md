Twicebreaker: Twilio-Powered Icebreaker Game
--------------------------------------------

An SMS-based game where the goal is to tag the most people you can in the
shortest amount of time by meeting them and texting back their unique tag code.
Whoever tags the most people wins!

## How It Works

 1. Enter your name and phone number in the form.
 2. The game will start when the admin pushes the start button.
 2. You will recieve a text with a unique code. This is your personal tag code.
 3. Go meet other people in the room, and reply back to the text with their tag
    code.
 4. The person who tags the most people within the time limit wins!

Powered by the [Twilio](http://twilio.com) API

## Installation / Setup

Clone repository in git in a new folder, and then run all the steps below:

### Create a .env File

This project uses [phpdotenv](https://github.com/vlucas/phpdotenv), and
requires a `.env` file with your environment configuration settings. Create a
file named `.env` in the project root with the following:

```
DATABASE_URL='mysql://root@localhost/twilio_icebreaker'
```

Replace or add in your own MySQL credentials as needed.

### Run The Migrations

From the project root, run:

```
php web/index.php -u db/migrate
```

### Great Success!

You should be good to go!

