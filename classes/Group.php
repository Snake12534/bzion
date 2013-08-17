<?php

class Group extends Controller {

    /**
     * The subject of the group
     * @var string
     */
    private $subject;

    /**
     * The time of the last message to the group
     * @var Date
     */
    private $last_activity;

    /**
     * The status of the group
     *
     * Can be 'active', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "groups";

    /**
     * Construct a new message
     * @param int $id The message's id
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $group = $this->result;

        $this->subject = $group['subject'];
        $this->last_activity = Date::parse($group['last_activity']);
        $this->status = $group['status'];
    }

    function getSubject() {
        return $this->subject;
    }

    function getLastActivity() {
        return $this->last_activity->diffForHumans();
    }

    /**
     * Get the URL that points to the group's page
     * @return string The group's URL, without a trailing slash
     */
    function getURL($dir="messages", $default=NULL) {
        return parent::getURL($dir, $default);
    }

    /**
     * Create a new message group
     *
     * @param string $subject The subject of the group
     * @param array $members A list of BZIDs representing the group's members
     * @return Group An object that represents the created group
     */
    public static function createGroup($subject, $members=array())
    {
        $query = "INSERT INTO groups(subject, last_activity, status) VALUES(?, NOW(), ?)";
        $params = array($subject, "active");

        $db = Database::getInstance();
        $db->query($query, "ss", $params);
        $groupid = $db->getInsertId();

        foreach ($members as $bzid) {
            $query = "INSERT INTO `player_groups` (`player`, `group`) VALUES(?, ?)";
            $db->query($query, "ii", array($bzid, $groupid));
        }

        return new Group($groupid);
    }

    /**
     * Get all the groups in the database a player belongs to that are not disabled or deleted
     * @param int $bzid The bzid of the player whose groups are being retrieved
     * @return array An array of group IDs
     */
    public static function getGroups($bzid) {
        $additional_query = "LEFT JOIN groups ON player_groups.group=groups.id
                             WHERE player_groups.player = ? AND groups.status
                             NOT IN (?, ?)";
        $params = array($bzid, "disabled", "deleted");

        return parent::getIds("groups.id", $additional_query, "iss", $params, "player_groups");
    }

    /**
     * Checks if a player belongs in the group
     * @param int $bzid The ID of the player
     * @return bool True if the player belongs in the group, false if they don't
     */
    public function isMember($bzid) {
        $result = $this->db->query("SELECT 1 FROM `player_groups` WHERE `group` = ?
                                    AND `player` = ?", "ii", array($this->id, $bzid));

        return count($result) > 0;
    }

}
