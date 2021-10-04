Setup:
1) Create a folder called `public/storage/gallery/Photos/California/2014` and add some jpg image files named: JapanLA_1, JapanLA_2, JapanLA_3, JapanLA_4, Aquarium_1, Aquarium_2, Aquarium_3, Aquarium_4, Aquarium_5, Aquarium_7, Aquarium_8

2) Run the migrations and seeders

I hope to:
- Have a command to generate thumbnails and medium sized images from original images dropped in the `public/storage/gallery` folder
    - it needs to tag the image (photo, digital, sketch), and date it (2021-08-31) **Should store them in a similar folder structure as the original files*
- Have a command to regenerate thumbnails from a tag (or all)
    - it should take thumbnail width/height as an option
- Have a gallery in Vue that is mobile friendly
- Recreate the breadcrumbs from the existing tags table
- Add pagination
- Update the tables to a proper structure
- Archive the gallery and create something new!
