# MotionCTA

## Overview

The project is a laravel 4 project with a vue frontend located in /vue-webpack, accessing the backend over a REST api.

## Installation

For Laravel you need composer and run ```composer install```

You will also need to set up a mysql database, install ffmpeg, ffprobe, youtube-dl and change the config in the .env accordingly.

Import the database.sql file into your database for now but later you can run clean migrations: https://laravel.com/docs/4.2/migrations#running-migrations

Also you need to install Laravel Passport ```php artisan passport:install --force```

Point your local php server to the /public folder, so that http://localhost:8888 shows what's in /public.

To start the vue-frontend navigate into /vue-webpack and run npm install

```
brew update
brew install npm
npm install
npm install eslint --save-dev
./node_modules/.bin/eslint --init
npm install babel-register babel-preset-env --save-dev
npm run dev
```

This will open a new browser window.

All the important files are in
- /vue-webpack/src
- /app
- /config
- /resources

## Important Api endpoints

### Video Upload and Transcoding

POST /api/video/before_upload

requires parameters:

- project_id
- filename (original filename with extension)
- title (optional, will default to filename)
- user_id (optional, only allowed in debug mode)

Returns 8 chars long string (video_id)

POST /api/video/transcoding_progress_report

requires:

- id (or video_id; for example: pZhCfHav)
- progress (integer, 1-100 allowed)


POST /api/video/success

requires

- id (or video_id)
- list_url (list of files)
- size_source
- size_out
- price
- bandwidth
- duration
