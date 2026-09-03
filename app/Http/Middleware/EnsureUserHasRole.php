<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. If not authenticated, redirect to login
        if (! $user) {
            return redirect()->route('login');
        }

        // 2. Normalize user role from database
        $userRole = strtolower(trim($user->role ?? ''));

        // 3. Parse and flatten comma-separated middleware parameters (e.g., 'role:Administrator,admin')
        $rawRoles = [];
        foreach ($roles as $roleParam) {
            foreach (explode(',', $roleParam) as $splitRole) {
                $cleaned = strtolower(trim($splitRole));
                if ($cleaned !== '') {
                    $rawRoles[] = $cleaned;
                }
            }
        }

        // 4. Apply alias mappings to handle interchangeable terms
        $allowedRoles = $rawRoles;

        // Admin aliases
        if (in_array('admin', $rawRoles, true) || in_array('administrator', $rawRoles, true)) {
            $allowedRoles[] = 'admin';
            $allowedRoles[] = 'administrator';
        }

        // Staff aliases
        if (in_array('staff', $rawRoles, true) || in_array('employee', $rawRoles, true)) {
            $allowedRoles[] = 'staff';
            $allowedRoles[] = 'employee';
        }

        $allowedRoles = array_unique($allowedRoles);

        // 5. Check Spatie or Custom RBAC Methods
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($allowedRoles)) {
            return $next($request);
        }

        if (method_exists($user, 'hasRole')) {
            foreach ($allowedRoles as $role) {
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }

        // 6. Direct column check ($user->role)
        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        // 7. Role mismatch fallback: Direct user to their appropriate homepage
        if (in_array($userRole, ['administrator', 'admin'], true)) {
            return redirect()->route('admin.offices.index')->with('error', 'Unauthorized area.');
        }

        if (in_array($userRole, ['employee', 'staff'], true)) {
            $staffRoute = $user->office_id ? route('dashboard.staff', ['officeId' => $user->office_id]) : route('dashboard');

            return redirect()->to($staffRoute)->with('error', 'Unauthorized area.');
        }

        return redirect()->route('dashboard')->with('error', 'You do not have permission to access this page.');
    }
}