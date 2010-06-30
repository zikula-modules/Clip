
  WARNING
	The upgrade to 0.2.1 removes the dependency of the image/upload plugins data
	to the PageMaster's upload path.
	Be sure to have it correct to update the data massively and without problems.
	This will let you to change the uploaddir and move the folder in the filesystem
	without worry about the plugins data integrity.
	0.2.1 also changes the upload path to a root-relative path,
	to build the URLs in the display with ease ;-)

	the pmform plugins needs to handle empty or null $data in postRead
