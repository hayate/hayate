<?php
/**
 * File        : HTML.php
 * Created     : Apr 3, 2011 8:05:01 PM
 * @author     : Andrea Belvedere
 * Description :
 */
class Hayate_HTML
{
    /**
     * Creates and HTML link
     *
     * @param string $path A URL or a path
     * @param string $text The text of the link, if empty the url will be used
     * @param string $title The title of the link
     * @param array $attributes Any extra a tag attribute
     * @return string The HTML link
     */
    static public function link($path, $text = null, $title = null, array $attributes = array())
    {
        $url = Hayate_URI::getInstance()->toUrl($path);
        $link = sprintf('<a href="%s"', $url);
        $link .= empty($title) ? '' : sprintf(' title="%s"', $title);
        foreach ($attributes as $key => $value)
        {
            $link .= sprintf(' %s="%s"', $key, $value);
        }
        $link .= '>';
        $link .= empty($text) ? $url : $text;
        $link .= '</a>';
        return $link;
    }

    public static function form($action = NULL, $method = 'post', $upload = FALSE, array $attribs = array())
    {
        if (NULL === $action)
        {
            $action = Hayate_URI::getInstance()->current();
        }
        $buf = '<form action="'. $action .'" method="'. $method .'"';
        if ($upload)
        {
            $buf .= ' enctype="multipart/form-data"';
        }
        foreach ($attribs as $key => $val)
        {
            $buf .= " {$key}=\"{$val}\"";
        }
        $buf .= '>';
        echo $buf."\n";
    }

    public static function closeForm()
    {
        echo "</form>\n";
    }

    public static function input($name = '', $value = '', $type = 'text', array $attribs = array())
    {
        $input = '<input type="'.$type.'"';
        if (! empty($name))
        {
            $input .= (' name="'.$name.'"');
        }
        $input .= (' value="'.$value.'"');

        foreach ($attribs as $key => $val)
        {
            $input .= " {$key}=\"{$val}\"";
        }
        $input .= '/>';
        echo $input ."\n";
    }
}
