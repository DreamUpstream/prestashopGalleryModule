## Prestashop 1.7 extra gallery module

### Features:

- Adds a gallery to the bottom of the product container
- Upload multiple images in the Admin office
- Configure some settings in module manager

### Installation:

- docker-compose up -d
- open localhost:8080
- connect to database with logins found in docker-compose.yml
- install Prestashop
- add extragallery to /modules
- install Extra Gallery in module catalog (installation may take some time on docker)

### Usage:

- first make sure to change gallery label text & maximum amount of images per product in the module configure section
- In admin side menu -> other select Extra Gallery
- press plus at the top right of the table and in the new window add images to specified product id (product id's can be found in Catalog-> Products)
- save and check out the new gallery on the product page
