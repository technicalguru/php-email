<?php

namespace TgEmail;

/**
 * An email attachment description.
 * <p>The actual content of the attachment must be available as a file.
 * @author ralph
 *        
 */
class Attachment {

    public const ATTACHED = 'default';
    public const EMBEDDED = 'embedded';
    
    /** The embedding type */
    public $type;
    public $name;
    public $cid;
    public $path;
    public $mimeType;
    public $deleteAfterSent;
    public $deleteAfterFailed;
    
    /**
     * Constructor.
     * @param string $type - the embedding type of this attachment
     * @param string $name - the name of the attachment as to be used in email
     * @param string $path - the path to the local file
     * @param boolean deleteAfterSent - whether to delete the file when the mail was sent
     * @param boolean deleteAfterFailed - whether to delete the file when sending of the email failed (requires deleteAfterSent = TRUE)
     */
    public function __construct($type, $name, $cid, $path, $mimeType, $deleteAfterSent = false, $deleteAfterFailed = false) {
        $this->type              = $type;
        $this->name              = $name;
        $this->cid               = $cid;
        $this->path              = $path;
        $this->deleteAfterSent   = $deleteAfterSent;
        $this->deleteAfterFailed = $deleteAfterFailed;
    }
    
    public static function from($a) {
        if (is_object($a)) {
            if (is_a($a, 'TgEmail\\Attachment')) {
                return $a;
            }
            return new Attachment($a->type, $a->name, $a->cid, $a->path, $a->mimeType, $a->deleteAfterSent, $a->deleteAfterFailed);
        }
        throw new EmailException('Cannot convert attachment');
    }
}

