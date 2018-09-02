// measurement data object, later filled and pushed to the api
let allData = {
  h: [],
  dd: [],
  ud: []
}

let previousDownKey, currentUpKey, currentDownKey, // keys needed for measurements
    wordInputWrapper, wordInput, wordDisplay, repetitionDisplay // DOM elements

let keystrokeTestID, quizID, courseId, studentHasRecord, studentHasImage // other global data variables

// default settings, later pulled from api
let word = 'loading', allRepetitions = 10, remainingRepetitions

// other variables, needed for computations and checks
let currentIndexWritten = 0, hDurations = [], ddDurations = [], udDurations = [], keyTimes = {}

const getUrlParam = (name) => {
  url = window.location.href
  name = name.replace(/[\[\]]/g, '\\$&')
  let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)')
  let results = regex.exec(url)
  if (!results) return null
  if (!results[2]) return ''
  return decodeURIComponent(results[2].replace(/\+/g, ' '))
}

const measureTimes = (e) => {
  recordDownUpDuration(e)
}

const recordDownUpDuration = (e) => {
  const kc = e.keyCode
  if (kc === 27 || kc === 16 || kc === 17 || kc === 18 || kc === 91 || kc === 93) return // not looking at shifts, esc, alt and ctrl

  // If the key DOWN-UP pattern wasn't yet recorded, initialize empty object
  if (!keyTimes[kc]) {
    keyTimes[kc] = {}
  }

  if (e.type === 'keydown') {

    currentDownKey = { key: keyCodes[kc], timestamp: Date.now()}

    // Record key down press timestamp if key is not yet being held
    if (!e.repeat) {
      keyTimes[kc].lastDown = Date.now()

      recordDownDownDuration(e) // compute DOWN-DOWN duration between this and the previous keydown
      recordUpDownDuration(e) // compute UP-DOWN duration between this and the previous keyup
    }

  } else if (e.type === 'keyup') {

    // Record key up press timestamp
    keyTimes[kc].lastUp = Date.now()

    const duration = keyTimes[kc].lastUp - keyTimes[kc].lastDown
    if (!isNaN(duration)) {
      hDurations.push({key: keyCodes[kc], duration})
    } else {
      resetAllVariables()
  
      wordInput.blur()
      $('.ui.basic.modal.restart').modal('show')
      setTimeout(() => {
        wordInput.value = ''
        wordInput.focus()
        $('.ui.basic.modal.restart').modal('hide')
      }, 750)
    }

    currentUpKey = { key: keyCodes[kc], timestamp: Date.now()}
    // after DOWN-UP pattern is complete, clear values for another possible measure
    keyTimes[kc] = {}

    checkCorrectCharacterWritten(e)
  }
}

const recordDownDownDuration = (e) => {
  if (previousDownKey) {
    ddDurations.push(
      {
        key1: previousDownKey.key,
        key2: currentDownKey.key,
        duration: Date.now() - previousDownKey.timestamp
      }
    )
  }

  previousDownKey = currentDownKey
}

const recordUpDownDuration = (e) => {
  if (currentUpKey) {
    udDurations.push(
      {
        key1: currentUpKey.key,
        key2: currentDownKey.key,
        duration: Date.now() - currentUpKey.timestamp
      }
    )
  }
}

// check if we're writing the correct letter, else restart
const checkCorrectCharacterWritten = (e) => {

  if (wordInput.value.charAt(currentIndexWritten) === word.charAt(currentIndexWritten)) {
    currentIndexWritten++
  } else {
    resetAllVariables()

    wordInput.blur()
    $('.ui.basic.modal.restart').modal('show')
    setTimeout(() => {
      wordInput.value = ''
      wordInput.focus()
      $('.ui.basic.modal.restart').modal('hide')
    }, 750)
  }
  checkIfEndOfInput(e)
}

const checkIfEndOfInput = () => {
  if (wordInput.value.length === word.length) {
    // check if everything was recorded
    if (ddDurations.length === hDurations.length-1 && udDurations.length === hDurations.length - 1) {
      remainingRepetitions--
      wordInputWrapper.classList.remove('loading')
      allData.h.push(hDurations)
      allData.dd.push(ddDurations)
      allData.ud.push(udDurations)
    } else {
      wordInput.blur()
      $('.ui.basic.modal.speed-restart').modal('show')
      setTimeout(() => {
        wordInput.value = ''
        wordInput.focus()
        $('.ui.basic.modal.speed-restart').modal('hide')
      }, 750)
    }

    resetAllVariables()
  }
  checkIfEndOfTest()
  repetitionDisplay.innerHTML = remainingRepetitions
}

const checkIfEndOfTest = () => {
  if (remainingRepetitions === 0) {
    $('#wordInputWrapper').hide()
    $('.password_display').hide()
    $('.progress_password_text').hide()
    $('#keystroke_finish_text').show()

    if (!studentHasImage) {
      $('#image-upload-inputs').show()
      startVideo()
    } else {
      const formData = new FormData()
      formData.append('user_id', window.currentUser)
      formData.append('quiz_id', quizID)
      formData.append('timing_matrix', JSON.stringify(convertToCSV(allData)))
      axios.post('http://localhost:8000/student/register', formData)
      .then((res) => {
        $('#image-upload-inputs').hide()
        $('#face_finish_text').show()
      })
    }

    document.querySelector('body').classList.add('test-complete')
  }
}

const resetAllVariables = () => {
  wordInput.value = ''
  currentIndexWritten = 0
  hDurations = []; ddDurations = []; udDurations = [];
  previousDownKey = null; currentDownKey = null; currentUpKey = null;
}

const convertToCSV = (kd) => {

  let outputMatrix = []

  // loop through all sessions
  for (let sessionNumber = 0; sessionNumber < kd.h.length; sessionNumber++) {
    let holdEntrySession = kd.h[sessionNumber]
    let outputVector = []

    for (let holdNumber = 0; holdNumber < holdEntrySession.length; holdNumber++) {
      let keyPress = holdEntrySession[holdNumber]
      let nextKeyPress

      // add the hold duration
      outputVector.push(keyPress.duration)

      // DD and UD are of the current key and the next one (if it exists)
      if (holdNumber !== holdEntrySession.length - 1) {

        nextKeyPress = holdEntrySession[holdNumber + 1]

        outputVector.push(kd.dd[sessionNumber][holdNumber].duration)
        outputVector.push(kd.ud[sessionNumber][holdNumber].duration)
      }
    }
    outputMatrix.push(outputVector)
  }

  return outputMatrix
}

window.onload = () => {
  let container = document.getElementById('insert_content_here')

  let user_id = container.dataset.user
  let quizId = container.dataset.quizId
  console.log(user_id)
  console.log(quizId)

  // get the DOM objects we need to change/record
  wordInputWrapper = document.getElementById('wordInputWrapper')
  wordInput = document.getElementById('wordInput')
  wordDisplay = document.getElementById('wordDisplay')
  repetitionDisplay = document.getElementById('remainingRepetitions')

  $('#wordInput').on('blur', () => {
    if (remainingRepetitions === 0) return false
    resetAllVariables()
  })

  // get the recording test settings from the central API
  axios.get('http://localhost:8000/quiz/' + quizId) // TODO: remove hardcoded number
    .then((response) => {
      word = response.data.course.keystroke_test_type.input_text,
      allRepetitions = response.data.course.keystroke_test_type.repetitions
      remainingRepetitions = allRepetitions
      keystrokeTestID = response.data.course.keystroke_test_type.id
      quizID = response.data.id
      courseId = response.data.course.id
      studentHasRecord = false
      studentHasImage = false

      axios.get('http://localhost:8000/course/' + courseId + '/student_status/' + user_id)
        .then(res => {
          studentHasRecord = res.data.has_record
          studentHasImage = res.data.has_picture

          if (studentHasRecord) {
            $('#wordInputWrapper').hide()
            $('.password_display').hide()
            $('.progress_password_text').hide()
            $('#has_record_text').show()
          }

          wordDisplay.innerHTML = word
          repetitionDisplay.innerHTML = remainingRepetitions
        })
    })

    window.currentUser = user_id

  wordInput.onkeydown = wordInput.onkeyup = measureTimes

}
  