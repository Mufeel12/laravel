<?php

use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$faker = \Faker\Factory::create();
		for ($p = 1; $p < 21; $p++) {
			$videos = \Illuminate\Support\Facades\DB::table('videos')->where('project', $p)->get();
			$total = 0;
			foreach ($videos as $video) {
				if (!$video->duration)
					$video->duration = 500;
				$cnt = $faker->randomElement([50, 62, 10, 2, 54, 1, 40, 100, 23, 35, 39, 21, 3, 69, 80, 300, 232, 219, 99, 84, 20, 6, 3, 6, 7, 8, 30, 24, 38]);
				$cnt1 = $faker->randomElement([15, 13, 1, 2, 3, 4, 8, 5, 6, 7, 9, 10, 11, 17, 12, 14, 16, 18, 19, 21, 20]);
				if ($cnt < $cnt1) {
					$cnt1 = $cnt - 1;
				}
				$total += $cnt;

				$uRefs = [];
				for ($i = 0; $i < 50; $i++)
					$uRefs[] = $faker->md5;
				for ($i = 0; $i < $cnt1; $i++) {
					$subscriber = \App\Subscriber::create();
					$subscriber->user_id = $video->owner;
					$subscriber->email = $faker->email;
					$subscriber->created_at = $faker->dateTimeInInterval($startDate = '-30 days', $interval = '+ 30 days');
					$subscriber->project_id = $video->project;
					$subscriber->team_id = $video->team;
					$subscriber->video_id = $video->id;
					$subscriber->firstname = $faker->firstName;
					$subscriber->lastname = $faker->lastName;
					$subscriber->phone_number = $faker->phoneNumber;
					$subscriber->tags = json_encode([$faker->randomElement($faker->words(5))]);
					$subscriber->user_agent = $faker->randomElement($uRefs);
					$subscriber->save();
				}
				// Seed video statistics
				for ($i = 0; $i < $cnt; $i++) {
					$kind = $faker->randomElement(['Mobile', 'Desktop', 'Tablet']);
					$browser = $faker->randomElement(['Chrome 77.0.3865', 'Firefox 50.0.343', 'Opera 22.11', 'Safari 66.03.3', 'Microsoft Edge 20.0.124']);
					$is_mobile = 0;
					if ($kind == 'Desktop') {
						$platform = 'Mac 10.15';
						$platform_version = '10.15';
					} else if ($kind == 'Desktop') {
						$platform = 'Windows 10';
						$platform_version = '10';
					} else if ($kind == 'Ubuntu') {
						$platform = 'Ubuntu 18';
						$platform_version = '18';
					} else if ($kind == 'Mobile') {
						$platform = 'Android 10.0';
						$platform_version = '10.0';
						$is_mobile = 1;
					} else if ($kind == 'Tablet') {
						$platform = 'iOS 10.15';
						$platform_version = '10.15';
						$is_mobile = 1;
					} else {
						$platform = 'UnKnown';
						$platform_version = 'UnKnown';
					}
					$time = $faker->dateTimeInInterval($startDate = '-30 days', $interval = '+ 30 days');
                    $watchStart = $faker->numberBetween(0, $video->duration - 5);
                    $watchEnd = $faker->numberBetween($watchStart, $video->duration);
					\App\Statistic::create([
						'video_id'               => $video->id,
						'project_id'             => $video->project,
						'user_id'                => $video->owner,
						'team_id'                => $video->team,
						'event'                  => 'video_view',
						'ip_address'             => $faker->ipv4,
						'unique_ref'             => $faker->randomElement($uRefs),
						'agents'                 => $faker->userAgent,
						'kind'                   => $kind,
						'platform'               => $platform,
						'platform_version'       => $platform_version,
						'is_mobile'              => $is_mobile,
						'browser'                => $browser,
						'latitude'               => $faker->latitude,
						'longitude'              => $faker->longitude,
						'country_code'           => $faker->countryCode,
						'domain'                 => $faker->domainName,
						'country_name'           => $faker->country,
                        'city'                   => $faker->city,
						'watch_start'            => $watchStart,
                        'watch_end'              => $watchEnd,
						'created_at'             => $time
					]);
				}
				// Seed cta statistics
				for ($i = 0; $i < $cnt; $i++) {
					$kind = $faker->randomElement(['Mobile', 'Desktop', 'Tablet']);
					$browser = $faker->randomElement(['Chrome 77.0.3865', 'Firefox 50.0.343', 'Opera 22.11', 'Safari 66.03.3', 'Microsoft Edge 20.0.124']);
					$is_mobile = 0;
                    if ($kind == 'Desktop') {
                        $platform = 'Mac 10.15';
                        $platform_version = '10.15';
                    } else if ($kind == 'Desktop') {
                        $platform = 'Windows 10';
                        $platform_version = '10';
                    } else if ($kind == 'Ubuntu') {
                        $platform = 'Ubuntu 18';
                        $platform_version = '18';
                    } else if ($kind == 'Mobile') {
                        $platform = 'Android 10.0';
                        $platform_version = '10.0';
                        $is_mobile = 1;
                    } else if ($kind == 'Tablet') {
                        $platform = 'iOS 10.15';
                        $platform_version = '10.15';
                        $is_mobile = 1;
                    } else {
                        $platform = 'UnKnown';
                        $platform_version = 'UnKnown';
                    }
					$event = $faker->randomElement(['email_capture', 'click', 'skip']);
					$eventInteractionGroup = $faker->randomElement(['before', 'during', 'after']);
					\App\Statistic::create([
						'video_id'                => $video->id,
						'project_id'              => $video->project,
						'user_id'                 => $video->owner,
						'team_id'                 => $video->team,
						'event'                   => $event,
						'ip_address'              => $faker->ipv4,
						'unique_ref'              => $faker->randomElement($uRefs),
						'agents'                  => $faker->userAgent,
						'kind'                    => $kind,
						'platform'                => $platform,
						'platform_version'        => $platform_version,
						'is_mobile'               => $is_mobile,
						'browser'                 => $browser,
						'latitude'                => $faker->latitude,
						'longitude'               => $faker->longitude,
						'country_code'            => $faker->countryCode,
                        'city'                    => $faker->city,
						'domain'                  => $faker->domainName,
						'country_name'            => $faker->country,
						'event_offset_time'       => $faker->numberBetween(0, $video->duration),
						'event_interaction_group' => $eventInteractionGroup,
						'created_at'              => $faker->dateTimeInInterval($startDate = '-30 days', $interval = '+ 30 days')
					]);
				}
			}
		}
	}
}
