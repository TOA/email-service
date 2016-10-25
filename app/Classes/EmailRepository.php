<?php

namespace App\Classes;

use App\Classes\Email;
use PDO;
use Swift_Message;

class EmailRepository
{
    /**
     * Email Sent
     */
    const STATUS_SENT       = 'sent';

    /**
     * Email Queued
     */
    const STATUS_QUEUED     = 'queued';

    /**
     * Email Canceled
     */
    const STATUS_CANCELED   = 'canceled';

    /**
     * Email Pending
     */
    const STATUS_PENDING    = 'pending';

    /**
     * @var PDO
     */
    protected $db;

    /**
     * EmailRepository constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param $to
     * @param $from
     * @param $subject
     * @param $body
     * @return Swift_Message
     */
    static public function factory($to, $from, $subject, $body)
    {
        $email = Swift_Message::newInstance($subject, $body)
            ->addTo($to)
            ->addFrom($from);

        return $email;
    }

    /**
     * @param Swift_Message $email
     * @param string $status
     * @return int
     */
    public function store(Swift_Message $email, $status = EmailRepository::STATUS_PENDING)
    {
        $message = $email->toString();

        $sql = "INSERT INTO email (`message`, `status`) VALUES (:message, :status)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    /**
     * @param $id
     * @param $status
     * @return string
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE email SET `status` = :status WHERE `id` = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function retrieve($id)
    {
        $sql = "SELECT * FROM email WHERE `id` = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * @param $id
     * @return int
     */
    public function cancel($id)
    {
        $sql = "UPDATE email SET status=:status WHERE id=:id";

        $status = EmailRepository::STATUS_CANCELED;

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }
}