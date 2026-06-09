<?php
declare(strict_types=1);

namespace VehicleTracker;

enum Role: string {
    case User  = 'user';
    case Admin = 'admin';
}
