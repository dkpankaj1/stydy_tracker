<?php

use App\Models\ChecklistItem;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('imports subject structure rows from csv for c plus plus subject', function () {
    $user = User::factory()->create();

    $subject = Subject::query()->create([
        'user_id' => $user->id,
        'name' => 'C++',
        'description' => null,
        'estimated_minutes' => 0,
    ]);

    $csv = implode("\n", [
        'subject,lesson,topic,checklist',
        'C++,Environment & Modern C++ Setup,Compiler Setup and Build Tools,Install latest GCC/Clang or Visual Studio with C++ workload',
        'C++,Environment & Modern C++ Setup,Compiler Setup and Build Tools,Verify compiler version supports C++17 or C++20',
        'C++,Environment & Modern C++ Setup,IDE and Debugging Setup,Configure VS Code or CLion for debugging',
        'C++,Core Syntax Refresh,Variables and Primitive Types,Revise int float double char bool',
    ]);

    $file = UploadedFile::fake()->createWithContent('structure.csv', $csv);

    $response = $this
        ->actingAs($user)
        ->post(route('subjects.import-structure', $subject), [
            'csv_file' => $file,
        ]);

    $response
        ->assertRedirect(route('subjects.show', $subject))
        ->assertSessionHasNoErrors();

    expect(Lesson::query()->where('subject_id', $subject->id)->count())->toBe(2);
    expect(Topic::query()->where('subject_id', $subject->id)->count())->toBe(3);
    expect(ChecklistItem::query()->where('subject_id', $subject->id)->count())->toBe(4);
});
