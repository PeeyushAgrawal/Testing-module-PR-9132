#### This is a testing module.
#### It is developed to test the PR#9132

 - This PR it to add be a hook on customer address form to add a custom field through a module
 - For this need to add following code in corresponding file

#### File - classes/form/CustomerAddressFormatter.php in getFormat() function before return
```
$additionalAddressFormFields = Hook::exec('additionalCustomerAddressFields', array(), null, true);
if (is_array($additionalAddressFormFields)) {

    foreach ($additionalAddressFormFields as $moduleName => $additionnalFormFields) {
        if (!is_array($additionnalFormFields)) {
            continue;
        }
        foreach ($additionnalFormFields as $formField) {
            $formField->moduleName = $moduleName;
            $format[$moduleName.'_'.$formField->getName()] = $formField;
        }
    }
}
```
 - To validate the field value the following code is need to add.

#### File - classes/form/CustomerAddressForm.php  add this function
```
    private function validateByModules()
    {

        $formFieldsAssociated = array();
        foreach ($this->formFields as $formField) {
            if (!empty($formField->moduleName)) {
                $formFieldsAssociated[$formField->moduleName][] = $formField;
            }
        }
        foreach ($formFieldsAssociated as $moduleName => $formFields) {
            if ($moduleId = Module::getModuleIdByName($moduleName)) {
                $validatedCustomerFormFields = Hook::exec('validateAddressFormFields', array('fields' => $formFields), $moduleId, true);
                if (is_array($validatedCustomerFormFields)) {
                    array_merge($this->formFields, $validatedCustomerFormFields);
                }
            }
        }
    }
```