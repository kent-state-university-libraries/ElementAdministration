# ElementAdministration

An Omeka plugin that allows site administrators to have more control of the "Add/Edit an Item" forms.

For each metadata element in the system, you can set:
<ul>
  <li>
    <strong>Form Label</strong> -
    <span>Override the label shown in the "Add/Edit an Item" forms.</span>
  </li>
  <li>
    <strong>Hidden on Item Edit Form</strong> -
    <span>Don't show this item on the admin item edit form.</span>
  </li>
  <li>
    <strong>Default Value</strong> -
    <span>Set a default value for this element in the "Add an Item" form.</span>
  </li>
  <li>
    <strong>Required</strong> -
    <span>Make this element required in the "Add/Edit an Item" forms.</span>
  </li>
  <li>
    <strong>Allow HTML</strong> -
    <span>Control whether to display the "HTML" checkbox for this element.</span>
  </li>
  <li>
    <strong>Allow Multiple Values</strong> -
    <span>Control whether to display the "Add Input" button for this element.</span>
  </li>
  <li>
    <strong>Public Label</strong> -
    <span>Override the label shown to the public for this element.</span>
  </li>
  <li>
    <strong>Hidden on Item Edit Form</strong> -
    <span>Don't show this item on the public site.</span>
  </li>
  <li>
    <strong>Brief Display</strong> -
    <span>Show this element in the "Brief Display" view of on your public item view.
      If no elements are selected for "Brief Display", all elements will be shown.
      If one or more are selected, a button will appear for this Element Set allowing users to view "Brief" or "Full" record displays
    </span>
  </li>
</ul>


## Installation

Clone this repository in your Omeka "plugins" directory.

```
git clone https://github.com/kent-state-university-libraries/ElementAdministration.git
```

After the repository is checkout out, you will need to login to Omeka and enable
the plugin.


## Configuration

Click the "Element Administration" link found in your admin menu, and edit every element you want to add some extra settings to.

------

Developed by [Kent State University Libraries](http://www.library.kent.edu).
