<?php
return array(
	'UserRegistrationConfirmationText' => 'Your account has been created, however you must first activate it. Please check your mailbox. You should receive the e-mail with the activation link within a few minutes.',
	'ProjectInvitationNoteText' => 'Here you can invite new people to the project. Just enter the e-mail address, the desired role and a custom note. If the invited person already has an account in the system, the invitation will show up in his or her profile. Otherwise, the peron will have to create an account first. Once registered, the invitation will show up immediately.',
	'GroupInvitationNoteText' => 'Here you can invite new people to the group. Just enter the e-mail address, the desired role and a custom note. If the invited person already has an account in the system, the invitation will show up in his or her profile. Otherwise, the peron will have to create an account first. Once registered, the invitation will show up immediately.',
	'AreaInvitationNoteText' => 'Here you can invite new people to the area. Just enter the e-mail address, the desired role and a custom note. If the invited person already has an account in the system, the invitation will show up in his or her profile. Otherwise, the peron will have to create an account first. Once registered, the invitation will show up immediately.',
	'InvitationAcceptedText' => 'The invitation has been accepted. Take a look at the top bar of the appplication. You should see a couple of new options there.',
	'InvitationFoundText' => 'The invitation has been added to your profile. You can now accept or reject it.',
	'InvitationNotFoundText' => 'The specified invitation cannot be found. Make sure that you have entered the invitation key exactly as it was provided in the e-mail message sent to you.',
	'InvitationRevokedText' => 'The invitation has been revoked and deleted.',
	
	// Public texts
	'CookieUsageInfoText' => 'This web application uses cookies solely for authentication purposes and keeping the information about the chosen language.',
	'AccountActivatedText' => 'Your account has been activated. You can now log in.',
	'PasswordRecoveryIntroductionText' => 'Please enter your credentials. You will receive an e-mail message with the link for changing your password.',
	'PasswordRecoveryEnterNewPasswordText' => 'Thank you, please enter the new password for your account. Remember to keep it complex and nontrivial!',
	'PasswordRecoverySuccessText' => 'Your password has been successfully changed. You can now log in with your new password.',
	'PasswordRecoveryFailureText' => 'This password recovery request is invalid.',
	
	// User profile management
	'ChangePasswordText' => 'To change the password, you must know your current password. In addition, a message is sent to your e-mail with the link to confirm the change.',
	'PhotoUsageText' => 'Your photo is shown in many different places, usually next to your content..',
	'PhotoUploadRulesText' => 'You can upload any image as big as 700x700 points in one of the following formats: JPG, PNG, GIF. The image is scaled down to sizes 128x128 px, 64x64 px, 32x32 px and 16x16 px. If the uploaded image is not a square, the central part is taken.',
	'YourCurrentEmailText' => 'Your current e-mail address for the account is:',
	'ChangeEmailText' => 'To change the e-mail address, you must enter your current password. In addition, a message is sent to your old e-mail with the link to confirm the change.',
	'ConfirmationLinkChangePasswordSentText' => 'The confirmation link to change the password has been sent to your e-mail address. Please open the message and click on it without logging out from the application.',
	'ConfirmationLinkChangeEmailSentText' => 'The confirmation link to change the e-mail has been sent to your old e-mail address. Please open the message and click on it without logging out from the application.',
	
	// Area group information
	'AreaNotAssignedToGroupMsg' => 'This area is not assigned to any group yet. Once the assignment is done, you will find here the information about the group you are a member of.',
	
	// Area profile editor
	'AreaProfileSaved' => 'The profile of your area has been saved.',
	
	// Project area request
	'CustomTextDisplayedToThisRequest' => 'Text displayed under the feedback box in the requests with this status:',
	
	// Project area
	'GroupUnassigned' => 'Unassigned',
	'CreateAreaText' => 'You can create a new area here, bypassing the area request process (if such a process is enabled in the project settings). No user will be assigned to the area after its creation. You have to do it manually, by choosing the menu option "Membership" in the area information page.',
	
	// Project area status
	'AreaStatusDescriptionFormText' => 'You can translate the status name in the translation file <code>statuses.[language code].php</code>. It will be displayed in the language of the given user. The label is the CSS class name from the visual theme. It defines how the status looks like.',
	
	// Project groups and categories
	'GroupCategoryDescriptionText' => 'Categories ease the group management. Create categories and assign the groups to them, and you will be able to filter the data through them on other pages.',
	
	// Invitation type forms
	'ProjectNominative: 0' => 'Project "0"',
	'GroupNominative: 0' => 'Group "0"',
	'AreaNominative: 0' => 'Area "0"',
	'FindInvitationPlaceholder' => 'Enter the invitation key to join it to your profile...',
	'AreaRequest: 0' => 'Request for area: 0',
	'PersonAlreadyInvitedErr' => 'The specified person is already invited here.',
	
	// Admin user management
	'UserRemovalQuestionText' => 'Do you really want to remove the user \'0\'? This will clear out all his personal information and membership, but the produced content will be kept intact. You will be able to restore the user at any time later, but the personal information and the membership would have to be configured again.',
	
	// Admin user registration
	'RegistrationRemovalQuestionText' => 'Do you really want to remove the registration request of user \'0\'?',
	'PruneRegistrationsQuestionText' => 'Do you want to remove all the registration requests older than 30 days?',
	'PrunedRegistrations: 0' => 'Pruned registrations: 0',
	
	// Membership management
	'NoteHintText' => 'We recommend entering here the function the member is responsible for - it will be displayed in the profile and the user lists.',
);