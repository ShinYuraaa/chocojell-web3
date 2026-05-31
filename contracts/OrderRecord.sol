// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

/**
 * OrderRecord Contract
 * Records cryptographic hashes of orders for immutable audit trail
 * Each order is recorded once with its hash for verification and tracking
 * 
 * Use case: E-commerce platform (ChocoJell)
 * - Record order_id hash when order is created
 * - Immutable proof that order existed at specific time
 * - Verify order integrity later if needed
 * 
 * Hash Format: keccak256(abi.encodePacked(orderId, customerId, totalPrice, orderDate))
 * This ensures order cannot be modified after recording
 */
contract OrderRecord {
    // Mapping: order_id -> order_hash (bytes32)
    mapping(uint256 => bytes32) public orderHashes;
    
    // Mapping: order_id -> recorded timestamp
    mapping(uint256 => uint256) public recordedAt;
    
    // Total orders recorded on this contract
    uint256 public totalOrdersRecorded;
    
    // Events for logging
    event OrderRecorded(
        uint256 indexed orderId,
        bytes32 indexed orderHash,
        uint256 customerId,
        uint256 totalPrice,
        uint256 timestamp,
        address indexed recordedBy
    );
    
    event OrderVerified(
        uint256 indexed orderId, 
        bytes32 orderHash, 
        bool isValid
    );
    
    /**
     * Record a new order hash to blockchain
     * Only records once per order ID (prevents duplicate recording)
     * 
     * @param _orderId Order ID from database
     * @param _customerId Customer ID from database
     * @param _totalPrice Total price in smallest unit (IDR cents or equivalent)
     * @param _orderHash Pre-calculated Keccak256 hash of order data
     * 
     * Requirements:
     * - Order ID must be > 0
     * - Order hash must not be empty (bytes32(0))
     * - Order must not already be recorded
     */
    function recordOrder(
        uint256 _orderId,
        uint256 _customerId,
        uint256 _totalPrice,
        bytes32 _orderHash
    ) external {
        require(_orderId > 0, "Invalid order ID");
        require(_orderHash != bytes32(0), "Invalid order hash");
        require(orderHashes[_orderId] == bytes32(0), "Order already recorded");
        
        orderHashes[_orderId] = _orderHash;
        recordedAt[_orderId] = block.timestamp;
        totalOrdersRecorded++;
        
        emit OrderRecorded(
            _orderId,
            _orderHash,
            _customerId,
            _totalPrice,
            block.timestamp,
            msg.sender
        );
    }
    
    /**
     * Get order hash from blockchain
     * @param _orderId Order ID to query
     * @return Order hash (bytes32(0) if not found)
     */
    function getOrderHash(uint256 _orderId) external view returns (bytes32) {
        return orderHashes[_orderId];
    }
    
    /**
     * Get order recorded timestamp
     * @param _orderId Order ID to query
     * @return Timestamp in seconds (0 if not recorded)
     */
    function getOrderTimestamp(uint256 _orderId) external view returns (uint256) {
        return recordedAt[_orderId];
    }
    
    /**
     * Verify order hash matches recorded hash
     * Useful for proving order integrity
     * 
     * @param _orderId Order ID to verify
     * @param _expectedHash Hash to verify against
     * @return isValid True if hash matches and order is recorded
     */
    function verifyOrderHash(uint256 _orderId, bytes32 _expectedHash) 
        external 
        returns (bool isValid) 
    {
        bytes32 storedHash = orderHashes[_orderId];
        isValid = storedHash == _expectedHash && storedHash != bytes32(0);
        
        emit OrderVerified(_orderId, _expectedHash, isValid);
        return isValid;
    }
    
    /**
     * Check if order is recorded
     * @param _orderId Order ID to check
     * @return True if order is recorded
     */
    function isOrderRecorded(uint256 _orderId) external view returns (bool) {
        return orderHashes[_orderId] != bytes32(0);
    }
    
    /**
     * Get total recorded orders count
     * @return Total count of orders recorded
     */
    function getTotalRecordedOrders() external view returns (uint256) {
        return totalOrdersRecorded;
    }
}
