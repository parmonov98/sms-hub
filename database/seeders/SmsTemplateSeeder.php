<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\SmsTemplate;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eskizProvider = Provider::where('display_name', 'eskiz')->first();
        
        if (!$eskizProvider) {
            $this->command->warn('Eskiz provider not found. Skipping template seeding.');
            return;
        }

        $templates = [
            [
                'name' => 'Welcome Message',
                'content' => 'Добро пожаловать в {service_name}! Ваш код подтверждения: {code}',
                'status' => 'pending',
                'variables' => ['service_name', 'code'],
            ],
            [
                'name' => 'Order Confirmation',
                'content' => 'Ваш заказ #{order_id} подтвержден. Сумма: {amount} сум. Спасибо за покупку!',
                'status' => 'pending',
                'variables' => ['order_id', 'amount'],
            ],
            [
                'name' => 'Password Reset',
                'content' => 'Код для сброса пароля: {reset_code}. Не передавайте его третьим лицам.',
                'status' => 'pending',
                'variables' => ['reset_code'],
            ],
            [
                'name' => 'Delivery Notification',
                'content' => 'Ваш заказ #{order_id} отправлен. Трек-номер: {tracking_number}. Ожидайте доставку.',
                'status' => 'pending',
                'variables' => ['order_id', 'tracking_number'],
            ],
            [
                'name' => 'Test Message',
                'content' => 'Это тест от Eskiz',
                'status' => 'approved',
                'variables' => [],
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                [
                    'provider_id' => $eskizProvider->id,
                    'name' => $template['name'],
                ],
                array_merge($template, [
                    'provider_id' => $eskizProvider->id,
                    'approved_at' => $template['status'] === 'approved' ? now() : null,
                ])
            );
        }

        $this->command->info('SMS templates seeded successfully.');
    }
}