<?php
namespace Kuink\Core;

class PersonGroupProperty {
	const ID = 'id';
	const UID = 'uid';
	const CODE = 'code';
	const NAME = 'name';
	const DISPLAY_NAME = 'display_name';
	const DESCRIPTION = 'description';
	const MAIL_NICK_NAME = 'mail_nickname';
	const EMAIL = 'email';

	const LOCATION = 'location';
  const ALLOW_EXTERNAL_SENDERS = 'allow_external_senders';
	const AUTO_SUBSCRIBE_NEW_MEMBERS = 'auto_subscribe_new_members';

  const VISIBILITY = 'visibility';

	const TOTAL_MEMBERS = 'total_members';
	const IS_OWNER = 'is_owner';
	const IS_MANAGER = 'is_manager';
	const IS_MEMBER = 'is_member';

	const _CREATION = '_creation';
	const _MODIFICATION = '_modification';
}