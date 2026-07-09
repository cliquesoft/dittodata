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

	Although not a single file project, this software boasts an impressive
	amount of features in a small footprint! Here's a short list of some
	functionality that can be utilized:

		o Mounts ext2/3/4, FAT, NTFS, and other filesystem formats
		o Mounts Linux (NFS) and Windows (CIFS) networked shares
		o Can use ssh or sshfs for networked and remote data
		o Automated ssh key installation with remote devices
		o Securely syncs remote data using ssh encryption
		o Jobs can be enabled or disabled
		o Jobs can be for the device or for a user
		o Pre and post-run script options for each job
		o Included alert.php script for email alerts
		o Ability to include tags in email alerts




# [EXAMPLE ACTIONS]

	Backup data using a config file:
	     backup --config=/home/user/personal.cfg

	Sync data between two mounted drives:
	     backup --name=DupeData --type=sync --include=/tmp/files.txt \
	       --source=/mnt/sda1 --target=/mnt/sdb3

	Backup updated data from a computer to a Windows server:
	     backup --name=backup --type=incremental --include=/tmp/files \
	       --source=/home/user --target='\\server\users\private' \
	       --targetUser=username --targetPass=password --targetDomn=mybiz

	Backup all data from a Linux network share to a remote device via ssh:
	     backup --name=migrate --type=full --include=/tmp/files.txt \
	       --source=//server/data --target='@1.2.3.4:/opt/dump' \
	       --targetUser=username --targetPass=password

	Restore select files from a Linux network share to a computer:
	     restore --name='Users' --files='/opt/wb.db,/home/user/my.pst' \
	       --overwrite=all --source=//server/data --target='/tmp' \
	       --sourceUser=username --sourcePass=password

	Show all the current backup jobs:
	     dittodata --show

	Show the configuration for a specific job:
	     dittodata --info --name='My Backup Job'

	Save a backup job to run once a month on the 1st @ 2:30am:
	     dittodata --save --name='A new job' --include=/etc/jobs/new.cfg \
	       --limit=12 --compression=bzip --type=full --alert=always \
	       --source=/home/user --target='%backup_server:/mnt/ext_drive' \
	       --targetUser=username --targetPass=password --hour=2 --min=30 \
	       --freq=monthly --list=1




# [FUTURE DEV TIMELINE]

	Since we are working with several many projects (13 on github alone),
	we are going to provide an anticipated timeline of releases using
	internal staff. Obviously outside contribution will advance these
	forecasted dates.

	2025 Oct - update pax; modify to work with (TC) TinyCore Linux
	2026 Jan - package dittodata for (TC) TinyCore Linux
	2026 Feb - completion of ModuleMaker for webWorks
	2026 Apr - migration of existing webWorks modules using ModuleMaker
	         - migration of Tracker into webWorks and deprecation of
	           of standalone project
	2026 May - update paged to 2018 code base from ACME
	         - apply any patches for bug fixes to existing projects
	2026 Jun - update web.libs for dittodata and web.de
	2026 Aug - move code from web.de into cli.de and update the former
	           to use the latter via XML communication
	2026     - rest of 2026 tbd

