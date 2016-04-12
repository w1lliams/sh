<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bican\Roles\Models\Role;

class Roles extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:roles';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'generate user roles';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $adminRole = Role::create([
      'name' => 'Admin',
      'slug' => 'admin',
      'description' => ''
    ]);

  }
}
