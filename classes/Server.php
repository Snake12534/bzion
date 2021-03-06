<?php

include_once("bzfquery.php");

class Server extends Controller
{

    /**
     * The name of the server
     * @var string
     */
    private $name;

    /**
     * The address of the server
     * @var string
     */
    private $address;

    /**
     * The bzid of the owner of the server
     * @var int
     */
    private $owner;

    /**
     * The server's bzfquery information
     * @var mixed
     */
    private $info;

    /**
     * The date of the last bzfquery of the server
     * @var string
     */
    private $updated;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "servers";

    /**
     * Construct a new Server
     * @param int $id The server's id
     */
    function __construct($id) {

        parent::__construct($id);
        $server = $this->result;

        $this->name = $server['name'];
        $this->address = $server['address'];
        $this->owner = $server['owner'];
        $this->info = unserialize($server['info']);
        $this->updated = new DateTime($server['updated']);

    }

    /**
     * Overload __set to update instance variables and database
     * @param string $name The variable's name
     * @param mixed $value The variable's new value
     */
    function __set($name, $value) {
        if ($name == 'name' || $name == 'address' || $name == 'info' || $name == 'updated') {
            $type = 's';
        } else if ($name == 'owner') {
            $type = 'i';
        }

        if ($name == 'info') {
            $value = serialize($value);
        }

        if (isset($type)) {
            $this->db->query("UPDATE ". $this->table . " SET " . $name . " = ? WHERE id = ?", $type."i", array($value, $this->id));
            $this->{$name} = $value;
        }

    }

    /**
     * Add a new server
     *
     * @param string $name The name of the server
     * @param string $address The address of the server (e.g: server.com:5155)
     * @param int $owner The BZID of the server owner
     * @return Mail An object that represents the sent message
     */
    static function addServer($name, $address, $owner) {
        $query = "INSERT INTO servers VALUES(NULL, ?, ?, ?, '', NOW())";
        $params = array($name, $address, $owner);

        $db = Database::getInstance();
        $db->query($query, "ssi", $params);

        $server = new Server($db->getInsertId());
        $server->forceUpdate();

        return $server;
    }

    /**
     * Update the server with current bzfquery information
     */
    function forceUpdate() {
        $this->info = bzfquery($this->address);
        $this->db->query("UPDATE servers SET info = ? WHERE id = ?", "si", array(serialize($this->info), $this->id));
    }

    /**
     * Checks if the server is online (listed on the public list server)
     * @return bool Whether the server is online
     */
    function isOnline() {
        $servers = file(LIST_SERVER);
        foreach ($servers as $server) {
            list($host, $protocol, $hex, $ip, $title) = explode(' ', $server, 5);
            if ($this->address == $host) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the server has players
     * @return bool Whether the server has any players
     */
    function hasPlayers() {
        return $this->info['numPlayers'] > 0;
    }

    /**
     * Gets the number of players on the server
     * @return int The number of players
     */
    function numPlayers() {
        return $this->info['numPlayers'];
    }

    /**
     * Gets the players on the server
     * @return array The players on the server
     */
    function getPlayers() {
        return $this->info['player'];
    }

    /**
     * Checks if the last update is older than the update interval
     * @return bool Whether the information is older than the update interval
     */
    function staleInfo() {
        $now = new DateTime("now");
        $last_update = $this->updated->diff($now);

        return $last_update->format('%i') > UPDATE_INTERVAL;
    }

    /**
     * Gets the server's ip address
     * @return string The server's ip address
     */
    function serverIp() {
        return $this->info['ip'];
    }

}
