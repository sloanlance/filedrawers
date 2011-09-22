<?php
require_once 'Zend/Form/Element/Multi.php';

class Filedrawers_Form_Element_Acl extends Zend_Form_Element_Multi
{
    /**
     * MultiCheckbox is an array of values by default
     * @var bool
     */
    protected $_isArray = true;

    protected $_rights = array();
    protected $_acl = array();

    public function setRights($rights)
    {
        $this->_rights = $rights;

        return $this;
    }

    public function setAcl($acl)
    {
        $this->_acl = $acl;

        return $this;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $html = '<table>'."\n";
        $html .= '<thead>'."\n\t".'<tr><th>'. $this->_rights[$this->_name]['label'] .'</th>';
        foreach ($this->_rights[$this->_name]['options'] as $option => $optionDesc) {
            $html .= '<th title="'. $optionDesc .'">'. $option .'</th>';
        }
        $html .= '</tr>'."\n".'</thead>'."\n";
        $html .= '<tbody>'."\n";
        foreach ($this->_acl as $member => $rights) {
            $html .= "\t".'<tr><td>'. $member .'</td>';
            foreach ($rights as $right => $value) {
                $html .= '<td><input type="checkbox" name="'. $this->_name .'['. $member .']['. $right .']" value="1"'. ($value ? ' checked="checked"' : '') .' /></td>';
            }
            $html .= '</tr>'."\n";
        }
        // add an extra row for adding a new member to the acl:
        $html .= "\t".'<tr><td><input type="text" name="'. $this->_name .'-new" /></td>';
        foreach ($this->_rights[$this->_name]['options'] as $option => $optionDesc) {
            $html .= '<td><input type="checkbox" name="'. $this->_name .'-new['. $option .']" value="1" /></td>';
        }
        $html .= '</tr>'."\n";

        $html .= '</tbody>'."\n";
        $html .= '</table>'."\n";

        return $html;
    }
}
