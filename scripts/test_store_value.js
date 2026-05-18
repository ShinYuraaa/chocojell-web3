const StoreValue = artifacts.require("StoreValue");

module.exports = async function (callback) {
  try {
    const accounts = await web3.eth.getAccounts();
    const contract = await StoreValue.deployed();

    const beforeValue = await contract.getValue();
    console.log("Current value:", beforeValue.toString());

    const tx = await contract.setValue(123, { from: accounts[0] });
    console.log("Tx hash:", tx.tx);

    const afterValue = await contract.getValue();
    console.log("Updated value:", afterValue.toString());

    callback();
  } catch (error) {
    callback(error);
  }
};
