// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

contract StoreValue {
    uint256 public value;

    event ValueUpdated(uint256 newValue, address indexed updatedBy);

    function setValue(uint256 _value) external {
        value = _value;
        emit ValueUpdated(_value, msg.sender);
    }

    function getValue() external view returns (uint256) {
        return value;
    }
}
