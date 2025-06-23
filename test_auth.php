<?php
require 'vendor/autoload.php';

try {
    $app = new Phast\System\Core\Application(__DIR__);
    
    // Test if we can resolve the UserEntity class
    $userEntityClass = \Phast\App\Modules\Auth\Models\Entities\UserEntity::class;
    echo "UserEntity class exists: " . (class_exists($userEntityClass) ? 'Yes' : 'No') . "\n";
    
    // Test if we can create a UserEntity instance
    $user = new $userEntityClass();
    echo "UserEntity instance created successfully\n";
    
    // Test if the getAuthPassword method exists
    if (method_exists($user, 'getAuthPassword')) {
        echo "getAuthPassword method exists\n";
    } else {
        echo "getAuthPassword method does NOT exist\n";
    }
    
    // Test if we can use static methods
    try {
        $allUsers = $userEntityClass::all();
        echo "Static all() method works: " . count($allUsers) . " users found\n";
    } catch (Exception $e) {
        echo "Static all() method failed: " . $e->getMessage() . "\n";
    }
    
    // Test if we can use where method
    try {
        $users = $userEntityClass::where('email', '=', 'test@example.com')->first();
        echo "Static where() method works\n";
    } catch (Exception $e) {
        echo "Static where() method failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 