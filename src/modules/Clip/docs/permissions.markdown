
Clip Permissions Scheme
========
Replace the `$variables` with your corresponding value or pattern.


module
--------
`Clip:: | ::`

To access to the admin panel  it's needed this admin permission level.  
In a later revision it will need admin permission to a pubtype, at least.


grouptype
--------
`Clip:g$gid: | ::`

Denying the access to a grouptype it won't be visible on the admin nor editor panel.  
The permission required to see them is overview access.  
This does not include its pubtypes, as they depends on their own permissions.

Example rules:

*   deny the access to the grouptype with ID = 3  
    `Clip:g3: | :: | NONE`

*   deny the access to many grouptypes  
    `Clip:g(4|5|12) | :: | NONE`


pubtype
--------
`Clip:$tid: | ::`

Denying the access to a pubtype will block all its publications too.  
It won't appear on the admin or editor panel either.

If the user has overview access to the pubtype, will be able to see the main and list screens,  
and if have read access will be able to access the display and edit views too,  
unless another previous rule block the access to a specific section, and vice versa.


main
--------
`Clip:$tid:main | ::$templateid`

The main screen can be controlled for different templates.

Example rules:

* only allow the access to the default main template of the pubtype 2  
  `Clip:2:main | ::clipdefault | OVERVIEW`

* only allow the access to the 'categories' main template of the pubtype 3  
  `Clip:3:main | ::categories | OVERVIEW`

* deny the access to any other main screen  
  `Clip:(2|3):main | :: | NONE`


list
--------
`Clip:$tid:list | ::$templateid`

The list screen can be controlled for different templates.

Example rules:

* only allow the access to the 'xml' list template of the pubtypes 7 and 10  
  `Clip:(7|10):list | ::xml | OVERVIEW`

* deny the access to any other list screen of the pubtype 7  
  `Clip:7:list | :: | NONE`


display
--------
`Clip:$tid:display | $pid::$templateid`

The control of the display screen determines which publications are available,  
through the pid, and also controls the allowed templates to render.

Example rules:

* deny the access to the 'print' display of pubs 1, 2 and 3 of the pubtype 5  
  `Clip:5:display | (1|2|3)::print | NONE`

* deny the access to display the publication with pid 5 of the pubtype 13  
  `Clip:13:display | 5:: | NONE`


edit
--------
`Clip:$tid:edit | $pid:$wfstate:$templateid`

The edit form can be controlled too by the publication pid,  
the workflow state of it, and the requested form template.

Example rules:

* deny the access to the approved publications of the pubtype 6  
  `Clip:6:edit | :approved: | NONE`

* deny the access to the 'special' edit form, of the publication 5 in the pubtype 21  
  `Clip:21:edit | 5::special | NONE`

* deny the access to the 'upgrade' edit form of the 'basic' state, in the pubtype 17  
  `Clip:17:edit | :basic:upgrade | NONE`


exec
--------
`Clip:$tid:exec | $pid:$wfstate:$actionid`

The workflow operations can be controlled per publication through the pid,  
and also globally with the workflow state and the action id.

The permission level required to edit or submit content depends of its workflow.

Example rules:

* deny the access to the operations of an approved publication of the pubtype 3  
  `Clip:3:exec | :approved: | NONE`

* deny the access to the 'update' operation, of the pubtypes 8 and 9  
  `Clip:(8|9):exec | ::update | NONE`

* deny the access to the 'upgrade' operation of the 'basic' state, in the pubtype 17  
  `Clip:17:exec | :basic:upgrade | NONE`


Mixing rules
--------
You can build some combos to allow or deny the access:

* allow the access only to the 'feedback' template and action of the pubtype 5  
  `Clip:5:(display|edit|exec) | ::feedback | READ`  
  `Clip:5: | :: | NONE`  
  assuming that the required level of 'feedback' is 'read' in the workflow xml.

* allow the edition and manipulation of approved publications only, of the pubtypes 8 and 9  
  `Clip:(8|9):(edit|exec) | :approved: | MODERATE`  
  the available operations will be the ones requiring access lower or equal to 'moderator'.



User Roles
========
They are recognized by Clip according the permission levels of the 'initial' state of the workflow.  
The lower level (0), is the level required to submit content, usually, the 'submit' operation.  
The next level (1), is interpreted by Clip as the moderator level, allowed to access the editor panel.  
The editors, authors and admins operations for each level relies on the access level required in the xml.

Final User
--------
As explained above, the public views requires overview and read access only.

Author
--------
Assumes level 0 of 'initial' as content submitter permission level.

Moderator
--------
Assumes level 1 of 'initial' as moderator permission level.

Editor
--------
Can be the same moderator for some simple workflows, but the operations can be different for the groups,  
according to the assigned permissions level versus the specified in the workflow.

Admin
--------
With admin access to a pubtype, a user is able to  
change the settings and all the operations from the admin panel.



Workflow Assumptions
========
You need to have some considerations while building a workflow.  
If you will handle many roles operating th publications,  
you need to take in account how Clip interprets the 'initial' state.

The available operations of the 'initial' state will tell Clip,  
who is able to submit content and who is able to enter to the editor panel.

The lower level of the 'initial' operations will be the level used,  
to access the submit form, and the available operations for that level will be shown as buttons.  
The next level will be interpreted as the moderator/editor level,  
and will be able to access the editor panel, viewing only the allowed operations for each group,  
that has that level or a higher one. The permission levels can define many roles accessing the editor panel.
