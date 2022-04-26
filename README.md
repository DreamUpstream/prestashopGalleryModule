## Prestashop 1.7 extra gallery module

### Features:

- Add an additional gallery to the bottom of the product page
- Upload multiple images in the Admin office
- Configure gallery look

### Installation:

- Install docker & run docker-compose up
- Open localhost:8080
- Connect to the database using logins found in docker-compose.yml
- Follow Prestashop installation
- Add extragallery folder to /modules
- Install Extra Gallery in module catalog (installation may take some time on docker)

### Usage:

- Change gallery label text & maximum amount of images per product in the module configure section
- In admin side menu -> other select Extra Gallery
- Press the plus button at the top right corner of the table and add images to your specified product id (product id's can be found in Catalog -> Products)
- Save and check out the new gallery on the selected product page
