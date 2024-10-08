module.exports = {
    apps: [
        {
            name: "queue worker",
            script: "php artisan queue:work --tries=2 --memory=384 --timeout=600",
            exec_mode: "fork",
            instances: 4,
        },
    ],
};
