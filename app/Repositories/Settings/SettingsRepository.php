<?php


namespace App\Repositories\Settings;


use App\Repositories\AbstractRepository;
use App\UserSettings;

class SettingsRepository extends AbstractRepository
{
	protected $model;
	
	public function __construct(UserSettings $userSettings)
	{
		parent::__construct($userSettings);
	}
	
	public function model()
	{
		return app(UserSettings::class);
	}
}
