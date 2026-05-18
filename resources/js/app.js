import './bootstrap';
import Web3 from "web3"; 

const web3 = new Web3("http://127.0.0.1:7545");

const contractAddress = "0x303d9c59330Ce7ca503A091Ce86F771E35d180f6";

const ABI = [
  {
    "inputs": [{"internalType":"uint256","name":"_value","type":"uint256"}],
    "name":"setValue",
    "outputs":[],
    "stateMutability":"nonpayable",
    "type":"function"
  },
  {
    "inputs":[],
    "name":"getValue",
    "outputs":[{"internalType":"uint256","name":"","type":"uint256"}],
    "stateMutability":"view",
    "type":"function"
  },
  {
    "inputs":[],
    "name":"value",
    "outputs":[{"internalType":"uint256","name":"","type":"uint256"}],
    "stateMutability":"view",
    "type":"function"
  }
];

const contract = new web3.eth.Contract(ABI, contractAddress);

window.getValue = async () => {
  const result = await contract.methods.getValue().call();
  console.log("Value:", result);
};

window.setValue = async () => {
  const accounts = await web3.eth.getAccounts();

  await contract.methods.setValue(100).send({
    from: accounts[0]
  });

  console.log("Berhasil set value");
};