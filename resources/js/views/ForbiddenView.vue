<template>
    <div class="row justify-content-center py-4 py-lg-5">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-lg-5">
                    <i class="bi bi-shield-lock text-danger display-4"></i>
                    <h4 class="mt-3">Access denied</h4>
                    <p class="text-body-secondary mb-4">
                        You do not have permission to access this section.
                    </p>
                    <RouterLink :to="{ name: 'dashboard' }" class="btn btn-primary">
                        Back to Dashboard
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
