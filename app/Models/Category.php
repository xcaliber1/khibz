<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MenuItem; // Import MenuItem model


class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}