Setup:
1) Create a folder called `storage/gallery` and add some jpg image files named: JapanLA_1, JapanLA_2, JapanLA_3, JapanLA_4, Aquarium_1, Aquarium_2, Aquarium_3, Aquarium_4, Aquarium_5, Aquarium_7, Aquarium_8

2) Run:
    - cp .env.example .env (create a database and update credentials)
    - php artisan key:generate
    - composer install
    - php artisan migrate
    - php artisan db:seed
    - php artisan images:generate
    - npm install & npm run dev

I hope to:
- [x] Have a command to generate thumbnails and medium sized images from original images dropped in the `storage/gallery` folder
    - it should use the seeded database to keep the original image and thumbnail paths
- [ ] Have a command to regenerate thumbnails from a tag (or all)
    - it should take thumbnail width/height as an option
- [x] Have a popup gallery in Vue/Tailwind
    - ideally mobile friendly
- [ ] Recreate the breadcrumbs from the existing tags table
- [ ] Add pagination
- [ ] Update the tables to a proper structure
- [ ] Archive the gallery and create something new!
