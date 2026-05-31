const OrderRecord = artifacts.require('OrderRecord');

module.exports = function(deployer) {
  deployer.deploy(OrderRecord);
};
