<?php
require_once '../config/Database.php';

class Election {
    private $conn;
    private $elections_table = 'elections';
    private $candidates_table = 'candidates';
    private $votes_table = 'votes';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // CREATE new election
    public function create($title, $description, $start_date, $end_date, $created_by) {
        if (empty($title) || empty($created_by)) {
            return ['success' => false, 'message' => 'Title and creator are required'];
        }

        $query = "INSERT INTO " . $this->elections_table . " 
                  (title, description, start_date, end_date, created_by, status) 
                  VALUES (?, ?, ?, ?, ?, 'draft')";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $created_by);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Election created successfully', 'election_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Failed to create election'];
        }
    }

    // GET all active elections
    public function getActiveElections() {
        $query = "SELECT e.*, 
                  (SELECT COUNT(*) FROM " . $this->votes_table . " WHERE election_id = e.id) as total_votes,
                  (SELECT COUNT(*) FROM " . $this->candidates_table . " WHERE election_id = e.id) as candidate_count
                  FROM " . $this->elections_table . " e 
                  WHERE e.status = 'active' 
                  AND NOW() BETWEEN e.start_date AND e.end_date
                  ORDER BY e.start_date DESC";
        
        $result = $this->conn->query($query);
        
        if (!$result) {
            return [];
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // GET election by ID
    public function getElectionById($election_id) {
        $query = "SELECT * FROM " . $this->elections_table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // GET candidates for an election
    public function getCandidates($election_id) {
        $query = "SELECT c.*, 
                  (SELECT COUNT(*) FROM " . $this->votes_table . " WHERE candidate_id = c.id) as vote_count
                  FROM " . $this->candidates_table . " c 
                  WHERE c.election_id = ? 
                  ORDER BY c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ADD candidate to election
    public function addCandidate($election_id, $name, $description, $position) {
        if (empty($name) || empty($election_id)) {
            return ['success' => false, 'message' => 'Name and election ID are required'];
        }

        $query = "INSERT INTO " . $this->candidates_table . " 
                  (election_id, name, description, position) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("isss", $election_id, $name, $description, $position);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Candidate added successfully', 'candidate_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Failed to add candidate'];
        }
    }

    // CAST vote
    public function castVote($election_id, $user_id, $candidate_id) {
        if (empty($election_id) || empty($user_id) || empty($candidate_id)) {
            return ['success' => false, 'message' => 'Missing required parameters'];
        }

        // Check if user already voted
        $query = "SELECT id FROM " . $this->votes_table . " 
                  WHERE election_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ii", $election_id, $user_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'You have already voted in this election'];
        }

        // Insert vote
        $query = "INSERT INTO " . $this->votes_table . " 
                  (election_id, user_id, candidate_id) 
                  VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("iii", $election_id, $user_id, $candidate_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Vote cast successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to cast vote'];
        }
    }

    // GET election results
    public function getResults($election_id) {
        $query = "SELECT c.id, c.name, c.position, c.description,
                  COUNT(v.id) as votes
                  FROM " . $this->candidates_table . " c
                  LEFT JOIN " . $this->votes_table . " v ON c.id = v.candidate_id
                  WHERE c.election_id = ?
                  GROUP BY c.id
                  ORDER BY votes DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // CHECK if user already voted
    public function hasUserVoted($election_id, $user_id) {
        $query = "SELECT id FROM " . $this->votes_table . " 
                  WHERE election_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $election_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // ACTIVATE election
    public function activateElection($election_id) {
        $query = "UPDATE " . $this->elections_table . " 
                  SET status = 'active' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $election_id);
        return $stmt->execute();
    }

    // CLOSE election
    public function closeElection($election_id) {
        $query = "UPDATE " . $this->elections_table . " 
                  SET status = 'closed' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $election_id);
        return $stmt->execute();
    }
}
?>