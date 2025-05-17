<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем три примера контактов
        Contact::create([
            'name' => 'Иван Петров',
            'email' => 'ivan.petrov@example.com',
            'phone' => '+7 (999) 123-45-67',
            'tags' => ['клиент', 'VIP'],
            'comment' => 'Постоянный клиент с 2020 года'
        ]);

        Contact::create([
            'name' => 'Мария Сидорова',
            'email' => 'maria.sidorova@example.com',
            'phone' => '+7 (999) 765-43-21',
            'tags' => ['партнер', 'дизайнер'],
            'comment' => 'Дизайнер интерьеров, сотрудничает с нами'
        ]);

        Contact::create([
            'name' => 'Алексей Иванов',
            'email' => 'alexey.ivanov@example.com',
            'phone' => '+7 (999) 987-65-43',
            'tags' => ['поставщик', 'мебель'],
            'comment' => 'Поставляет мебель для проектов'
        ]);
    }
}
