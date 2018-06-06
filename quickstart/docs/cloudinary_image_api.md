
# PHP API doc

http://cloudinary.com/documentation/php_integration#getting_started_guide

# Photo path convention

- env := production|staging|testing
  - production = e-p
  - staging = e-s
  - testing = e-t
* scope := store|user|global-site
  - store = s-s
  - user = s-u
  - global-site = s-g

* under store scope
  - individual store = s-<id>
  - misc resource  = s-<id>/m/xxx
  - product pictures = s-<id>/p/p-<id>/xxx

## examples

  - for store logo: e-p/s-s/s-3877/m/logo.jpg
  - for product:    e-p/s-s/s-3877/p/p-id/<xxx>.jpg

  - for user: e-s/s-u/u-1234/...
  * for site: e-p/s-g/...


# code update

 - `quickstart/application/scripts/upload_converted_pictures.php`
   - remove
 - `quickstart/public/js/modules/create_products_lightbox.js`
   `quickstart/public/js/controller/selling_products.js`
   - update: remove converted pictures
 - upload pictures to cloudinary in backend(while saving/updating products)
 - migration script
   - update old images to cloudinary, and update info im db
 - frontend
   functions to get picture url

# upgrade and migration

## what happends when a user uploads a picture?
- the picture is uploaded to filepicker with filepickers widget, and we get a
  filepicker-url for this picture at the front end(javascript)

- we post the filepicker-url to our backend, then we generate a uuid for this
  picture, then:

  - for store logo: we generate a path(folder) for misc files with the store_id,
  e.g. `e-s/s-s/s-<store_id>/m/`, and upload the file as
  `e-s/s-s/s-<store_id>/m/<uuid>.jpg`, after that we set the `converted_logo` field
  of the store to <uuid>, so when get a store, we can get its logo on cloudinary
  with its `store_id` and `converted_logo`(uuid)

  - for product picture, we also generate a uuid, then upload it as:
    `e-s/s-s/s-<stiore_id>/p/p-<product_id>/<uuid>.jpg`, then we save the uuid into
    the `name` field of the picture(table pictures in store db). we can get the picture
    with store_id, product_id and uuid.

- If a store's converted logo is not a uuid (empty or a URL starts with `http`), we can say
  its logo is not on cloudinary.

- If a product picture's name is empty(not a uuid), we can say this picture is not on coudinary.

## steps

- create job
  run `quickstart/application/scripts/create_picture_migrate_job.php`, it will create a job for each
  store with status = ACTIVATED or with created time greater than 2014-03-01

- do the job
  run `quickstart/application/scripts/migrate_pictures_to_cloudinary.php`, this script will get jobs
  from the job db and do it(store by store):

  - upload the store logo to cloudinary if the store has one, the store cache will be updated
    automatically after the logo upload.

  - go through every pictures in the pictures table, upload it (or its biggest converted pictures) to
    cloudinary, the clear the cache of the product which these pictures belong to
