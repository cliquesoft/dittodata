# [PREAMBLE]

	Thanks for taking interest in dittodata! This project duplicates data
	between two points, whether local or remote, allowing several methods
	including mounted disks, ssh, sshfs, rsync. The main purpose for this
	software was for data backup, but it can be used for any reason such
	as migration of data between servers or other devices, or any other
	reason that data would need to be duplicated.




# [FOR THE IMPATIENT]

	To Install:
		1. cd /path/to/dittodata
		2. sudo cp ./bin/* /bin
		3. sudo cp ./etc/* /etc

	To Use:
		1. dittodata --help




# [USEFUL FEATURES]

	Being a single file usually does not include a rich feature set, but
	this script was able to pack quite a bit in! Here's a short list of
	some rather robust actions that can be performed:

		o Archive data using before or after dates
		o Clean mismatched data between directories or drives
		o Deduplicate data between one or two directories
		o Remove empty directories
		o Each action can ignore files by date specifications
		o Hidden directories and files can optionally be included
		o Dryruns can test results before implementation
		o Each action can move or delete data matching criteria
		o Directory paths can be manipulated when moving data

	Although not a single file project, this software boasts an impressive
	amount of features in a small footprint! Here's a short list of some
	functionality that can be utilized:

		o Mounts ext2/3/4, FAT, NTFS, and other filesystem formats
		o Mounts Linux (NFS) and Windows (CIFS) networked shares
		o Uses rsync, ssh, and sshfs for networked and remote data
		o Automated ssh key installation with remote devices
		o Securely syncs remote data using ssh encryption
		o Jobs can be enabled or disabled
		o Jobs can be for the device or for a user
		o Pre and post-run script options for each job
		o Included alert.php script for email alerts


