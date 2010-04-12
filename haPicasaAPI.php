<?php
/**
 * haPicasaReader
 * 
 * @author bu <bu@hax4.in>
 */

class haPicasaAPI
{
    protected $_cache_dir = 'tmp/';
    protected $_username = '';
    
    public function __construct($username)
    {
        $this->_username = $username;
    }
    
    public function getAlbums()
    {
        $picasa_obj = $this->_getXMLObj('http://picasaweb.google.com.tw/data/feed/base/user/'.$this->_username.'?alt=rss&kind=album&hl=en_US&access=public');
        
        $albums = array();
        
        foreach($picasa_obj->channel->item as $item)
        {
            preg_match('#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im', $item->description , $matches);
            
            $albums[] =
            array(
                'link' => (string) $item->link,
                'title'=> (string) $item->title,
                'guid' => (string) str_replace('?alt=rss&hl=en_US','',str_replace('http://picasaweb.google.com.tw/data/entry/base/user/'.$this->_username.'/albumid/','',$item->guid)),
                'thumbnail' => (string) $matches[2]
            );
        }
        
        return $albums;
    }
    
    public function getAlbumPhotos($guid, $thumbnail_size = '128')
    {
        $picasa_obj = $this->_getXMLObj('http://picasaweb.google.com.tw/data/feed/base/user/'.$this->_username.'/albumid/'.$guid.'?alt=rss&kind=photo&hl=en_US');

        $photos = array();
        
        foreach($picasa_obj->channel->item as $item)
        {
            $data = (array) $item->enclosure;
            $data = $data['@attributes'];
            
            $image = $data['url'];
            $title = $item->title;
            $thumbnail = substr($image,0,strrpos($image,'/')).'/s'.$thumbnail_size.'/'.substr($image,strrpos($image,'/')+1,strlen($image)-strrpos($image,'/'));
        
            $photos[] = array('title'=>$title, 'image'=>$image,'thumbnail'=>$thumbnail);
        }
        
        return $photos;
    }
    
    protected function _getXMLObj($url)
    {        
        if(file_exists($this->_cache_dir. md5($url).'_'.date('YmdH'.'.cache.php')))
        {
            $xmlString = base64_decode(include $this->_cache_dir. md5($url).'_'.date('YmdH').'.cache.php');
        }
        else
        {
            $fileString = base64_encode(file_get_contents($url));
            file_put_contents($this->_cache_dir. md5($url).'_'.date('Ymd_H').'.cache.php',$fileString);
            $xmlString = base64_decode($fileString);
        }
        
        return new SimpleXMLElement($xmlString);
    }
}
