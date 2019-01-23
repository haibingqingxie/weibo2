<?php

use Illuminate\Database\Seeder;

use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $user = User::first();
        $user_id = $user->id;

        // 获取去除1号的所有用户id数组
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();

        // 1号关注所有其他用户
        $user->follow($follower_ids);

        // 其他所有用户关注1号用户
        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }
    }
}
